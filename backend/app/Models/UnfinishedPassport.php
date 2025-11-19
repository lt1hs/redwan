<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnfinishedPassport extends Model
{
    use HasFactory;

    protected $fillable = [
        'gender',
        'full_name',
        'nationality',
        'passport_number',
        'passport_id',
        'date_of_birth',
        'residence_expiry_date',
        'expiration_date',
        'phone_number',
        'mobile_number',
        'transaction_type',
        'residence_authority',
        'address',
        'governorate',
        'zipcode',
        'najacode',
        'personal_photo',
        'passport_photo',
        'residence_photo',
        'passport_extension_photo',
        'notes',
        'completion_status'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'residence_expiry_date' => 'date',
        'expiration_date' => 'date',
    ];
}
