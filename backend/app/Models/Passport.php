<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Passport extends Model
{
    protected $fillable = [
        'full_name',
        'nationality',
        'passport_number',
        'date_of_birth',
        'residence_expiry_date',
        'phone_number',
        'mobile_number',
        'passport_status',
        'passport_delivery_date',
        'transaction_type',
        'payment_status',
        'delivered_by',
        'address',
        'zipcode',
        'personal_photo',
        'passport_photo',
        'unique_code',
        'email',
        'sponsor_name',
        'relationship',
        'extension_reason',
        'barcode',
        'signature_data',
        'no_signature',
        'no_signature_reason'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'passport_delivery_date' => 'date',
        'no_signature' => 'boolean'
    ];
}