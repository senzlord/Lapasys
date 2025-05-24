<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanPayment extends Model
{
    protected $fillable = [
        'loan_id',
        'payment_date',
        'amount',
        'note',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}
