<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $fillable = [
        'contract_number',
        'contract_date',
        'contract_type', // New field
        'contract_place',
        'husband_name',
        'husband_nationality',
        'husband_id_number',
        'husband_birth_date', // New field
        'husband_phone',
        'husband_address',
        'husband_passport_number', // New field
        'wife_name',
        'wife_nationality',
        'wife_id_number',
        'wife_birth_date', // New field
        'wife_phone',
        'wife_address',
        'wife_passport_number', // New field
        'present_dowry', // Replaces dowry_amount
        'deferred_dowry', // New field
        'husband_conditions_arabic', // New field
        'husband_conditions_persian', // New field
        'wife_conditions_arabic', // New field
        'wife_conditions_persian', // New field
        'first_witness', // New field
        'second_witness', // New field
        'officiant_name',
        'notes'
    ];

    protected $casts = [
        'contract_date' => 'date',
        'husband_birth_date' => 'date',
        'wife_birth_date' => 'date',
        'present_dowry' => 'decimal:2',
        'deferred_dowry' => 'decimal:2'
    ];
}
