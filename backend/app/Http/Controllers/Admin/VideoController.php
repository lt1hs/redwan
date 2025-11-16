<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

class VideoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Video::with('categories:id,name,slug')->latest()->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $video = Video::create($request->all());
            if ($request->filled('tags'))
                $video->syncTags($request->tags);
            $video->categories()->sync($request->categories);
            if ($request->filled('featured_image')) {
                $video->addMediaFromBase64($request->featured_image)->usingFileName(uniqid() . '.jpg')->toMediaCollection('featured_image', 'public');
            }
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Video $video)
    {
        $video->load(['categories']);
        $video->categories = $video->categories->pluck('id');

        $video->unsetRelation('categories');
        $tags = $video->tags->pluck('name');
        $video->unsetRelation('tags');
        $video = $video->toArray();

        $video["tags"] = $tags;
        return $video;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Video $video)
    {
        return DB::transaction(function () use ($request, $video) {
            $video->update($request->all());
            if ($request->filled('tags'))
                $video->syncTags($request->tags);
            $video->categories()->sync($request->categories);
            $video->updateOrDeleteMediaFromBase64Request($request, 'featured_image', 'public');
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Video $video)
    {
        $video->delete();
    }
}
