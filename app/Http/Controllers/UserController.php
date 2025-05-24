<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'username' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($request->role);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log("Created new user: {$user->name}");

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function showJson(User $user)
    {
        try {
            $logs = Activity::with('causer')
            ->where('subject_type', \App\Models\User::class)
            ->where('subject_id', $user->id)
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'description' => $log->description,
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                    'causer' => $log->causer ? ['name' => $log->causer->name] : null,
                ];
            });

            return response()->json([
                'user' => $user,
                'salary' => $user->salary ?? (object)[
                    'base_salary' => 0,
                    'total_allowance' => 0,
                    'total_salary' => 0
                ],
                'biodata' => $user->biodata ?? (object)[
                    'phone' => '',
                    'address' => '',
                    'birth_date' => '',
                    'gender' => ''
                ],
                'logs' => $logs,
                'loans' => $user->loans ?? (object)[
                    'id' => '',
                    'total_amount' => '',
                    'installments' => '',
                    'monthly_amount' => '',
                    'status' => '',
                ],
                'employment' => $user->employmentPeriods ?? (object)[
                    'id' => '',
                    'start_date' => '',
                    'end_date' => '',
                    'status' => '',
                ],
            ]);
        } catch (\Throwable $e) {
            \Log::error('Error in showJson: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }

    public function updateBiodata(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date_format:Y-m-d'],
            'gender' => ['nullable', 'in:male,female'],
        ]);

        $user->update([
            'name' => $request->name,
        ]);

        $user->biodata()->updateOrCreate([], [
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'birth_date' => $validated['birth_date'] ?? null,
            'gender' => $validated['gender'] ?? null,
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log("Updated biodata");

        return response()->json(['status' => 'ok']);
    }

    public function addEmployment(Request $request, User $user)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        // optional: check if there's an active employment already
        $hasActive = $user->employmentPeriods()->whereNull('end_date')->exists();
        if ($hasActive && !$validated['end_date']) {
            return response()->json(['message' => 'User already has an active employment.'], 422);
        }

        $employment = $user->employmentPeriods()->create($validated);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($employment)
            ->log('Employment created ' . $validated['start_date'] . ' to ' . $validated['end_date']);

        return response()->json(['employment' => $employment]);
    }
    public function updateEmployment(Request $request, User $user, $employmentId)
    {
        $employment = $user->employmentPeriods()->findOrFail($employmentId);

        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $employment->update($validated);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($employment)
            ->log('Employment ' . $employment->id . ' updated date range ' . $validated['start_date'] . ' to ' . $validated['end_date']);

        return response()->json(['employment' => $employment]);
    }

    public function updateSalary(Request $request, User $user)
    {
        $validated = $request->validate([
            'base_salary' => 'required|numeric|min:0',
            'total_allowance' => 'nullable|numeric|min:0',
            'total_salary' => 'required|numeric|min:0',
        ]);

        $user->salary()->updateOrCreate([], $validated);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log('Updated salary ' . $validated['base_salary'] . ' + ' . $validated['total_allowance'] . ' = ' . $validated['total_salary'] . ' for ' . $user->name);

        return response()->json(['status' => 'ok']);
    }

    public function storeLoan(Request $request, User $user)
    {
        $data = $request->validate([
            'total_amount' => 'required|numeric|min:10000',
            'installments' => 'required|integer|min:1|max:12',
            'description' => 'nullable|string',
            'start_month' => 'required|date_format:Y-m',
        ]);

        $data['monthly_amount'] = round($data['total_amount'] / $data['installments'], 2);
        $data['start_month'] .= '-01'; // convert to full date

        $loan = $user->loans()->create($data);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($loan)
            ->withProperties(['amount' => $loan->total_amount])
            ->log('Loan created ' . $loan->total_amount . ' for ' . $user->name . ' with ' . $loan->installments . ' installments');

        return response()->json(['loan' => $loan]);
    }
}
