<?php

namespace App\Models;

use App\Dijlah\UpdateOrDeleteMedia;
use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\MediaLibrary\MediaCollections\File;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Tags\HasTags;

class Video extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use UpdateOrDeleteMedia;
    use HasSlug;
    use HasTags;

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

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Video $video) {
            if (!$video->date)
                $video->date = new Carbon;
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
