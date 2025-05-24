<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $fillable = [
        'user_id',
        'description',
        'total_amount',
        'installments',
        'monthly_amount',
        'current_paid_months',
        'start_month',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(LoanPayment::class);
    }

    public function getRemainingInstallmentsAttribute()
    {
        return $this->installments - $this->current_paid_months;
    }

    public function getRemainingAmountAttribute()
    {
        return $this->total_amount - ($this->monthly_amount * $this->current_paid_months);
    }
}
