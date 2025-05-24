<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PayrollRecord;
use App\Models\Loan;
use App\Models\LoanPayment;

use App\Helpers\PayrollHelper;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);
        $date = Carbon::createFromDate($year, $month, 1);

        $users = User::with([
            'roles',
            'salary',
            'loans' => function ($q) use ($date) {
                $q->where('status', 'active')->where('start_month', '<=', $date->format('Y-m-d'));
            },
            'loans.payments',
            'payrollRecords' => function ($q) use ($date) {
                $q->where('payroll_month', $date->format('Y-m-d'));
            }
        ])->get();

        $filteredUsers = $users->filter(function ($user) use ($date) {
            return $user->employmentPeriods->filter(function ($period) use ($date) {
                return
                    $period->start_date->lte($date->copy()->endOfMonth()) &&
                    (is_null($period->end_date) || $period->end_date->gte($date->copy()->startOfMonth()));
            })->isNotEmpty();
        });

        $filteredUsers->each(function ($user) use ($date) {
            $record = $user->payrollRecords->first();

            if ($record) {
                $user->loan_deduction_final = $record->loan_deduction;
                $user->take_home_final = $record->take_home;
            } else {
                $loanTotal = $user->calculateLoanDeduction($date);
                $base = $user->salary->base_salary ?? 0;
                $allow = $user->salary->total_allowance ?? 0;
                // dd($date,$loanTotal, $base, $allow);
                $user->loan_deduction_final = $loanTotal;
                $user->take_home_final = $base + $allow - $loanTotal;
            }
        });

        return view('payroll.index', [
            'users' => $filteredUsers,
            'month_label' => $date->translatedFormat('F Y'),
            'selectedMonth' => $month,
            'selectedYear' => $year,
        ]);
    }

    public function checkProrate(User $user, Request $request)
    {
        $date = Carbon::createFromDate(
            $request->query('year', now()->year),
            $request->query('month', now()->month),
            1
        );

        $should = PayrollHelper::shouldProrate($user, $date);

        return response()->json([
            'should' => $should,
            'prorated_salary' => $should
                ? PayrollHelper::calculateProratedSalary($user, $date)
                : null,
            'full_salary' => (int) ($user->salary->base_salary ?? 0),
        ]);
    }

    public function pay(Request $request, User $user)
    {
        $validated = $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
            'is_prorated' => 'nullable|boolean',
        ]);

        $payDate = Carbon::createFromDate($validated['year'], $validated['month'], 1);

        if ($user->biodata && $user->biodata->join_date && $payDate->lt(Carbon::parse($user->biodata->join_date))) {
            return response()->json([
                'message' => 'Cannot pay salary before user join date: ' . Carbon::parse($user->biodata->join_date)->format('F Y')
            ], 422);
        }

        DB::beginTransaction();

        try {
            $salary = $user->salary;
            $base = $validated['is_prorated']
                ? \App\Helpers\PayrollHelper::calculateProratedSalary($user, $payDate)
                : ($salary->base_salary ?? 0);
            $loanTotal = 0;
            $loanIdsPaid = [];

            if ($request->filled('loan_override')) {
                foreach ($request->loan_override['ids'] as $loanId) {
                    $amount = $request->loan_override['values'][$loanId] ?? 0;
                    if ($amount > 0) {
                        $loan = Loan::findOrFail($loanId);
                        $loan->payments()->create([
                            'payment_date' => now(),
                            'amount' => $amount,
                            'note' => 'Paid via payroll ' . $payDate->format('F Y'),
                        ]);

                        $loan->increment('current_paid_months');

                        if ($loan->current_paid_months >= $loan->installments) {
                            $loan->status = 'paid';
                            $loan->save();
                        }

                        $loanTotal += $amount;
                        $loanIdsPaid[] = $loan->id;
                    }
                }
            } else {
                $loan = $user->loans()
                            ->where('status', 'active')
                            ->where('start_month', '<=', $payDate->format('Y-m-d'))
                            ->first();

                if ($loan) {
                    $loanTotal = $loan->monthly_amount;
                    $loan->payments()->create([
                        'payment_date' => now(),
                        'amount' => $loan->monthly_amount,
                        'note' => 'Paid via payroll ' . $payDate->format('F Y'),
                    ]);
                    $loanIdsPaid[] = $loan->id;
                }
            }

            $record = PayrollRecord::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'payroll_month' => $payDate->format('Y-m-d'),
                ],
                [
                    'base_salary' => $base,
                    'allowance' => $salary->total_allowance ?? 0,
                    'loan_deduction' => $loanTotal,
                    'take_home' => $base + ($salary->total_allowance ?? 0) - $loanTotal,
                    'status' => 'paid',
                    'is_prorated' => $validated['is_prorated'] ?? false,
                ]
            );

            if (!empty($loanIdsPaid)) {
                $user->loans()->whereIn('id', $loanIdsPaid)->get()->each(function ($loan) {
                    if ($loan->current_paid_months >= $loan->installments) {
                        $loan->status = 'paid';
                        $loan->save();
                    }
                });
            }

            activity()->causedBy(auth()->user())
                ->performedOn($user)
                ->log("Paid salary for " . $payDate->format('F Y'));

            DB::commit();

            return response()->json(['message' => 'Salary paid successfully.']);
        } catch (\Throwable $e) {
            DB::rollBack();

            \Log::error('Payroll payment error: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json(['message' => 'Payroll failed: ' . $e->getMessage()], 500);
        }
    }

    public function loanJson(User $user, Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);
        $date = Carbon::createFromDate($year, $month, 1);

        return $user->loans()
            ->with('payments') // Load payments to calculate correctly
            ->where('status', 'active')
            ->where('start_month', '<=', $date->format('Y-m-d'))
            ->get()
            ->map(function ($loan) use ($date) {
                $start = Carbon::parse($loan->start_month)->startOfMonth();
                $current = $date->copy()->startOfMonth();

                $monthsPassed = $start->lte($current)
                    ? $start->diffInMonths($current) + 1
                    : 0;

                $expectedTotal = min($monthsPassed, $loan->installments) * $loan->monthly_amount;
                $paid = $loan->payments->sum('amount');
                $remaining = max(0, $expectedTotal - $paid);

                return [
                    'id' => $loan->id,
                    'total_amount' => $loan->total_amount,
                    'installments' => $loan->installments,
                    'monthly_amount' => $remaining, // use dynamic value here
                ];
            });
    }
}
