<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder; // Import Builder for type hinting scopes
use Illuminate\Database\Eloquent\Relations\MorphTo; // For polymorphic relations if needed later, or general relation type


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
        'status', // e.g., 'sent', 'failed', 'pending'
        'error',
        'response_data', // Raw JSON response from SMS gateway
        'message_id',    // Message ID from SMS gateway (e.g., MeliPayamak's RecId)
        'retries',       // Number of retry attempts

        // Custom fields for context
        'type',           // e.g., 'passport_created', 'general', 'auth_code'
        'related_id',     // ID of the related model (e.g., passport_id)
        'recipient_name', // Name of the recipient for easier identification
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'related_id' => 'integer',
        'retries' => 'integer',
        'message_id' => 'string', // Message ID from SMS gateway is often a string
        'response_data' => 'array', // Cast JSON string from DB to PHP array/object
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // --- Scopes for easier querying ---

    /**
     * Scope a query to only include sent SMS.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope a query to only include failed SMS.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to only include pending SMS.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include SMS from today.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', now()->toDateString());
    }

    // --- Relationships (if you want to link SMS logs to other models) ---

    /**
     * Get the associated model based on 'type' and 'related_id'.
     *
     * This method dynamically resolves the related model.
     * Consider using Laravel's polymorphic relationships (morphTo) if
     * you frequently link SMS logs to multiple different types of models.
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function related(): ?Model
    {
        if (!$this->related_id || !$this->type) {
            return null;
        }

        // Define a mapping from your 'type' field values to actual Model classes.
        // This is flexible, but requires manual updates if new types are added.
        $typeToModel = [
            'passport_created' => \App\Models\Passport::class,
            'passport_approved' => \App\Models\Passport::class,
            'passport_rejected' => \App\Models\Passport::class,
            'passport_pending' => \App\Models\Passport::class,
            // Example: 'user_registered' => \App\Models\User::class,
            // Add more mappings as needed for other related models
        ];

        $modelClass = $typeToModel[$this->type] ?? null;

        // Ensure the model class exists before attempting to find it.
        if ($modelClass && class_exists($modelClass)) {
            return $modelClass::find($this->related_id);
        }

        return null;
    }
}