<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Salary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'base_salary',
        'total_allowance',
        'total_salary',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Optional: derived accessor
    public function getTakeHomeFormattedAttribute()
    {
        return number_format($this->total_salary, 0, ',', '.');
    }
}