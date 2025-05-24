<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmploymentPeriod extends Model
{
    protected $fillable = [
        'user_id',
        'start_date',
        'end_date'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
