<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Carbon\Carbon;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function biodata()
    {
        return $this->hasOne(Biodata::class);
    }

    public function salary()
    {
        return $this->hasOne(Salary::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function calculateLoanDeduction(Carbon $date)
    {
        $loanTotal = 0;
        foreach ($this->loans as $loan) {
            $start = Carbon::parse($loan->start_month)->startOfMonth();
            $current = $date->copy()->startOfMonth();
            $monthsPassed = $start->lte($current)
                ? $start->diffInMonths($current) + 1
                : 0;

            $expected = min($monthsPassed, $loan->installments) * $loan->monthly_amount;
            $paid = $loan->payments->sum('amount');
            $loanTotal += max(0, $expected - $paid);
        }

        return $loanTotal;
    }

    public function payrollRecords()
    {
        return $this->hasMany(PayrollRecord::class);
    }

    public function employmentPeriods()
    {
        return $this->hasMany(EmploymentPeriod::class);
    }
}
