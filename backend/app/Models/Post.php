<?php

namespace App\Models;

use App\Dijlah\UpdateOrDeleteMedia;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\MediaCollections\File;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Tags\HasTags;
use Laravel\Scout\Searchable;

class Post extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use HasTags;
    use HasSlug;
    use Searchable;
    use UpdateOrDeleteMedia;

    protected $guarded = ['id', 'updated_at', 'created_at'];
    protected function casts(): array
    {
        return [
            'show_on_homepage' => 'boolean',
            'is_published' => 'boolean',
            'date' => 'datetime',
        ];
    }

    protected $appends = ['featured_image_url'];
    protected $hidden = ['media'];


    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->usingLanguage('')
            ->doNotGenerateSlugsOnUpdate();
    }

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    public static function normalizeString($string)
    {
        return strtr($string, array('۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9', '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9', 'ة' => 'ه', 'ـ' => ''));
    }

    public function toSearchableArray(): array
    {
        // algolia needs this normalizion, don't know about meilisearch
        return [
            'title' => $this->normalizeString($this->title),
            'slug' => $this->normalizeString($this->slug),
            'description' => $this->normalizeString($this->description),
            'content' => $this->normalizeString($this->content),
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Post $post) {
            if (!$post->date)
                $post->date = new Carbon;
        });
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('featured_image')
            ->acceptsFile(function (File $file) {
                return $file->mimeType === 'image/jpeg';
            })
            ->singleFile();
    }

    public function getFeaturedImageUrlAttribute()
    {
        return $this->getFirstMediaUrl('featured_image');
    }

    public function categories(): MorphToMany
    {
        return $this->morphToMany(Category::class, 'categorizable');
    }
}
