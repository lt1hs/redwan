<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'phone',
        'message',
        'status',
        'type',
        'related_id',
        'recipient_name',
        'error',
        'response_data',
        'message_id',
        'retries'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'related_id' => 'integer',
        'retries' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope a query to only include sent SMS.
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope a query to only include failed SMS.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to only include pending SMS.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include SMS from today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', now()->toDateString());
    }

    /**
     * Get the associated model based on type.
     */
    public function related()
    {
        if (!$this->related_id || !$this->type) {
            return null;
        }

        // Map SMS types to model classes
        $typeToModel = [
            'passport_created' => 'App\Models\Passport',
            'passport_approved' => 'App\Models\Passport',
            'passport_rejected' => 'App\Models\Passport',
            'passport_pending' => 'App\Models\Passport',
            // Add more mappings as needed
        ];

        if (!isset($typeToModel[$this->type])) {
            return null;
        }

        $modelClass = $typeToModel[$this->type];
        return $modelClass::find($this->related_id);
    }
} 