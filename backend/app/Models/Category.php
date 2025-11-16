<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Category extends Model implements Sortable
{
    use HasFactory;
    use SortableTrait;
    use HasSlug;

    protected $guarded = ['id', 'updated_at', 'created_at'];
    protected $hidden = ['pivot'];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->usingLanguage('')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function posts(): MorphToMany
    {
        return $this->morphedByMany(Post::class, 'categorizable');
    }

    public function videos(): MorphToMany
    {
        return $this->morphedByMany(Video::class, 'categorizable');
    }

    /*
    https://github.com/spatie/eloquent-sortable

    If your model/table has a grouping field (usually a foreign key): id, user_id, title, order_column and you'd like the above methods to take it into considerations, you can create a buildSortQuery method at your model:
    */
    public function buildSortQuery()
    {
        return static::query()->where('type', $this->type);
    }
}
