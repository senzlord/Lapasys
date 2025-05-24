<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\User;

class PayrollHelper
{
    public static function shouldProrate(User $user, Carbon $payDate): bool
    {
        $startOfMonth = $payDate->copy()->startOfMonth();
        $endOfMonth = $payDate->copy()->endOfMonth();

        return $user->employmentPeriods->contains(function ($period) use ($startOfMonth, $endOfMonth) {
            $startDate = Carbon::parse($period->start_date);
            $endDate = $period->end_date ? Carbon::parse($period->end_date) : null;

            // Prorate jika:
            // 1. Masuk bulan ini
            $joinedThisMonth = $startDate->between($startOfMonth, $endOfMonth);

            // 2. Keluar bulan ini
            $resignedThisMonth = $endDate && $endDate->between($startOfMonth, $endOfMonth);

            return $joinedThisMonth || $resignedThisMonth;
        });
    }

    public static function calculateProratedSalary(User $user, Carbon $payDate): int
    {
        $baseSalary = $user->salary?->base_salary ?? 0;
        $daysInMonth = $payDate->daysInMonth;

        $start = $payDate->copy()->startOfMonth();
        $end = $payDate->copy()->endOfMonth();

        // Cari periode kerja yang aktif di bulan ini
        $period = $user->employmentPeriods
            ->first(function ($p) use ($start, $end) {
                return Carbon::parse($p->start_date)->lte($end) &&
                    (is_null($p->end_date) || Carbon::parse($p->end_date)->gte($start));
            });

        if (!$period) return 0;

        $activeStart = Carbon::parse($period->start_date)->greaterThan($start) ? Carbon::parse($period->start_date) : $start;
        $activeEnd = $period->end_date
            ? (Carbon::parse($period->end_date)->lessThan($end) ? Carbon::parse($period->end_date) : $end)
            : $end;

        $workedDays = $activeStart->diffInDays($activeEnd) + 1;

        return round(($baseSalary / $daysInMonth) * $workedDays);
    }
}
