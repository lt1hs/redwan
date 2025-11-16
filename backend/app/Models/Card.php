<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Card extends Model
{
    protected $fillable = [
        'full_name_fa',
        'father_name_fa',
        'full_name_en',
        'father_name_en',
        'passport_number',
        'national_id',
        'police_code',
        'card_expiry_date',
        'personal_photo',
        'passport_photo',
        'passport_id',
        'card_type',
        'parent_card_id',
        'status',
        'citizenship_fa',
        'citizenship_en',
        'relative_relation',
        'unique_code'
    ];

    protected $casts = [
        'card_expiry_date' => 'date',
    ];

    // Card types
    const TYPE_PERSONAL = 'personal';
    const TYPE_WIFE = 'wife';
    const TYPE_SON = 'son';
    const TYPE_DAUGHTER = 'daughter';

    // Relative Relations
    const RELATION_IMMIGRANT_WIFE = 'immigrant_wife';
    const RELATION_IMMIGRANT_SON = 'immigrant_son';
    const RELATION_IMMIGRANT_DAUGHTER = 'immigrant_daughter';

    // Status types
    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the passport associated with the card
     */
    public function passport(): BelongsTo
    {
        return $this->belongsTo(Passport::class);
    }

    /**
     * Get the parent card if this is a family member card
     */
    public function parentCard(): BelongsTo
    {
        return $this->belongsTo(Card::class, 'parent_card_id');
    }

    /**
     * Get all family member cards associated with this card
     */
    public function familyCards(): HasMany
    {
        return $this->hasMany(Card::class, 'parent_card_id');
    }

    /**
     * Check if the card is for a family member
     */
    public function isFamilyMember(): bool
    {
        return in_array($this->card_type, [self::TYPE_WIFE, self::TYPE_SON, self::TYPE_DAUGHTER]);
    }

    /**
     * Check if the card is expired
     */
    public function isExpired(): bool
    {
        return $this->card_expiry_date->isPast();
    }

    /**
     * Get the card type in Arabic/Persian
     */
    public function getCardTypeInArabic(): string
    {
        return match($this->card_type) {
            self::TYPE_PERSONAL => 'شخصي',
            self::TYPE_WIFE => 'زوجة',
            self::TYPE_SON => 'ابن',
            self::TYPE_DAUGHTER => 'ابنة',
            default => $this->card_type,
        };
    }

    /**
     * Get the card type in English
     */
    public function getCardTypeInEnglish(): string
    {
        return match($this->card_type) {
            self::TYPE_PERSONAL => 'Personal',
            self::TYPE_WIFE => 'Wife',
            self::TYPE_SON => 'Son',
            self::TYPE_DAUGHTER => 'Daughter',
            default => $this->card_type,
        };
    }

    /**
     * Scope a query to only include active cards
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Update card status based on expiry date
     */
    public function updateStatus(): void
    {
        if ($this->isExpired() && $this->status === self::STATUS_ACTIVE) {
            $this->update(['status' => self::STATUS_EXPIRED]);
        }
    }

    // Relationship with dependent cards (for family members)
    public function dependentCards(): HasMany
    {
        return $this->hasMany(Card::class, 'parent_card_id');
    }
} 