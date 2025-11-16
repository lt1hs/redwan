<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Speech extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'recipient',
        'content',
        'paper_size',
        'header_image',
        'footer_image',
        'signature_image',
    ];

    protected $appends = [
        'header_image_url',
        'footer_image_url',
        'signature_image_url',
    ];

    public function getHeaderImageUrlAttribute()
    {
        return $this->header_image ? asset('storage/' . $this->header_image) : null;
    }

    public function getFooterImageUrlAttribute()
    {
        return $this->footer_image ? asset('storage/' . $this->footer_image) : null;
    }

    public function getSignatureImageUrlAttribute()
    {
        return $this->signature_image ? asset('storage/' . $this->signature_image) : null;
    }
}