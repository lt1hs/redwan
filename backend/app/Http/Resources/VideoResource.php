<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'categories' => $this->categories->map(function ($category) {
                return [
                    'slug' => $category->slug,
                    'name' => $category->name,
                ];
            }),
            'tags' => $this->tags->map(function ($tag) {
                return ['name' => $tag->name, 'slug' => $tag->slug];
            }),
            'date' => $this->date->format('Y-m-d H:i:s'),
            'featured_image_url' => $this->featured_image_url,
            'content' => $this->content
        ];
    }
}
