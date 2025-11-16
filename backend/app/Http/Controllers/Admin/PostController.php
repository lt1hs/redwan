<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Post::with('categories:id,name,slug')->latest()->get(['id', 'title', 'date', 'slug']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $post = Post::create($request->except(['tags', 'categories']));
            if ($request->filled('tags'))
                $post->syncTags($request->tags);

            $post->categories()->sync($request->categories);
            if ($request->filled('featured_image')) {
                $post->addMediaFromBase64($request->featured_image)->usingFileName(uniqid() . '.jpg')->toMediaCollection('featured_image', 'public');
            }
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        $post->load(['categories', 'tags']);
        $post->categories = $post->categories->pluck('id');

        $post->unsetRelation('categories');
        $tags = $post->tags->pluck('name');
        $post->unsetRelation('tags');
        $post = $post->toArray();

        $post["tags"] = $tags;
        return $post;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        return DB::transaction(function () use ($request, $post) {
            $post->update($request->all());
            $post->categories()->sync($request->categories);
            if ($request->filled('tags'))
                $post->syncTags($request->tags);
            $post->updateOrDeleteMediaFromBase64Request($request, 'featured_image', 'public');
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $post->delete();
    }
}
