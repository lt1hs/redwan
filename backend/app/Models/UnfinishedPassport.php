<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnfinishedPassport extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'nationality',
        'passport_number',
        'date_of_birth',
        'residence_expiry_date',
        'phone_number',
        'mobile_number',
        'transaction_type',
        'address',
        'zipcode',
        'personal_photo',
        'passport_photo',
        'notes',
        'completion_status'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'residence_expiry_date' => 'date',
    ];
}
