<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollRecord extends Model
{
    protected $fillable = [
        'user_id',
        'payroll_month',
        'base_salary',
        'allowance',
        'loan_deduction',
        'take_home',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
