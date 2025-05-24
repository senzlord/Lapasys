<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
class Biodata extends Model
{
    protected $table = 'biodata';
    protected $fillable = [
        'user_id',
        'phone',
        'address',
        'birth_date',
        'join_date',
        'gender',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getAgeAttribute()
    {
        return Carbon::parse($this->birth_date)->age;
    }

}
