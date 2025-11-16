<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Http\Resources\VideoResource;
use App\Models\Category;
use App\Models\Navigation;
use App\Models\Option;
use App\Models\Page;
use App\Models\Post;
use App\Models\Slider;
use App\Models\Video;
use Illuminate\Database\Eloquent\Builder;

class PublicApiController extends Controller
{
    public function general()
    {
        return [
            "navigation" => Navigation::first() ?? [],
            "social_networks" => Option::where('key', 'social_networks')->first()->value ?? [],
            "search_targets" => [
                "posts" => Post::orderBy('date', 'desc')->where('is_published', true)->get(['id', 'title', 'slug']),
                "videos" => Video::orderBy('date', 'desc')->where('is_published', true)->get(['id', 'title', 'slug']),
            ]
        ];
    }

    public function landing()
    {
        $categories = Category::where('type', 'posts')->has('posts')->get(['id', 'name', 'slug']);
        $posts = Post::orderBy('date', 'desc')->with(['categories:id,name,slug', 'tags'])->where('is_published', true)->where('show_on_homepage', true)->select(['id', 'title', 'slug', 'description', 'content', 'date'])->limit(3)->get();
        $videos = Video::orderBy('date', 'desc')->with(['categories:id,name,slug', 'tags'])->where('is_published', true)->where('show_on_homepage', true)->select(['id', 'title', 'slug', 'description', 'content', 'date'])->limit(10)->get();

        $posts->transform(function ($post) {
            $tags = $post->tags->map(function ($tag) {
                return ['name' => $tag->name, 'slug' => $tag->slug];
            });
            $post = $post->toArray();
            $post['tags'] = $tags;
            return $post;
        });
        $videos->transform(function ($video) {
            $tags = $video->tags->map(function ($tag) {
                return ['name' => $tag->name, 'slug' => $tag->slug];
            });
            $video = $video->toArray();
            $video['tags'] = $tags;
            return $video;
        });

        return [
            "posts" => $posts,
            "videos" => $videos,
            "categories" => $categories,
            "slides" => Slider::first()->slides ?? [],
        ];
    }












    public function page($slug)
    {
        $page = Page::where('is_published', true)->where('slug', $slug)->firstOrFail();
        return $page;
    }

    public function posts()
    {
        return PostResource::collection(Post::where('is_published', true)->orderBy('date', 'desc')->jsonPaginate());
    }

    public function postsCategories()
    {
        $categories = Category::ordered()->where('type', 'posts')->get(['id', 'name', 'slug']);
        return $categories;
    }

    public function postsCategoriesWithContent()
    {
        $categories = Category::ordered()->where('type', 'posts')->whereHas('posts', function (Builder $query) {
            $query->where('is_published', true);
        })->with('posts')
            ->get(['id', 'name', 'slug'])->transform(function ($category) {
                return [...$category->toArray(), "posts" => PostResource::collection($category->posts->take(3))];
            });
        return $categories;
    }

    public function postsGetByCategories($ids)
    {
        $ids = explode(',', $ids);

        return PostResource::collection(Post::where('is_published', true)->whereHas('categories', function (Builder $query) use ($ids) {
            $query->whereIn('id', $ids);
        })->with(['categories:id,name,slug', 'tags'])->get());
    }


    public function postsGetByIds($ids)
    {
        $ids_array = explode(',', $ids);

        return PostResource::collection(Post::with(['categories:id,name,slug', 'tags'])->where('is_published', true)->whereIn("id", $ids_array)->select(['id', 'title', 'slug', 'description', 'content', 'date'])->orderByRaw("FIELD(id, " . $ids . ")")->get());
    }



    public function categoryPosts($slug)
    {
        $category = Category::where('type', 'posts')->where('slug', $slug)->select(['id', 'name', 'slug'])->firstOrFail();
        return PostResource::collection($category->posts()->where('is_published', true)->orderBy('date', 'desc')->jsonPaginate())->additional(['category' => $category]);
    }

    public function post($slug)
    {
        $post = Post::where('is_published', true)->where('slug', $slug)->with('categories:id,name,slug')->select(['id', 'title', 'slug', 'description', 'content', 'date'])->firstOrFail();
        $tags = $post->tags->map(function ($tag) {
            return ['name' => $tag->name, 'slug' => $tag->slug];
        });
        $post = $post->toArray();
        $post['tags'] = $tags;
        unset($post['media']);
        return $post;
    }










    public function videos()
    {
        return VideoResource::collection(Video::where('is_published', true)->orderBy('date', 'desc')->jsonPaginate());
    }

    public function videosCategories()
    {
        $categories = Category::ordered()->where('type', 'videos')->get(['id', 'name', 'slug']);
        return $categories;
    }

    public function videosCategoriesWithContent()
    {
        $categories = Category::ordered()->where('type', 'videos')->whereHas('videos', function (Builder $query) {
            $query->where('is_published', true);
        })->with('videos')
            ->get(['id', 'name', 'slug'])->transform(function ($category) {
                return [...$category->toArray(), "videos" => VideoResource::collection($category->videos->take(3))];
            });
        return $categories;
    }

    public function videosGetByIds($ids)
    {
        $ids_array = explode(',', $ids);

        return VideoResource::collection(Video::with(['categories:id,name,slug', 'tags'])->where('is_published', true)->whereIn("id", $ids_array)->select(['id', 'title', 'slug', 'description', 'content', 'date'])->orderByRaw("FIELD(id, " . $ids . ")")->get());
    }

    public function categoryVideos($slug)
    {
        $category = Category::where('type', 'videos')->where('slug', $slug)->select(['id', 'name', 'slug'])->firstOrFail();
        return VideoResource::collection($category->videos()->where('is_published', true)->orderBy('date', 'desc')->jsonPaginate())->additional(['category' => $category]);
    }

    public function video($slug)
    {
        $video = Video::where('is_published', true)->where('slug', $slug)->with('categories:id,name,slug')->select(['id', 'title', 'slug', 'description', 'content', 'date', 'video'])->firstOrFail();
        $tags = $video->tags->map(function ($tag) {
            return ['name' => $tag->name, 'slug' => $tag->slug];
        });
        $video = $video->toArray();
        $video['tags'] = $tags;
        unset($video['media']);
        return $video;
    }

    public function tag($slug)
    {
        $posts = PostResource::collection(Post::orderBy('date', 'desc')->withAnyTags([$slug])->with(['categories:id,name,slug', 'tags'])->where('is_published', true)->select(['id', 'title', 'slug', 'description', 'content', 'date'])->get());
        $videos = VideoResource::collection(Video::orderBy('date', 'desc')->withAnyTags([$slug])->with(['categories:id,name,slug', 'tags'])->where('is_published', true)->select(['id', 'title', 'slug', 'description', 'content', 'date'])->get());

        return [
            'posts' => $posts,
            'videos' => $videos
        ];
    }
}
