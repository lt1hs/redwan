<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use ZipArchive;
use Illuminate\Database\Eloquent\Builder;

class PublicWebsiteController extends Controller
{


    function downloadPostCategories(Request $request, $ids)
    {
        // Prepare zip file
        $zip = new ZipArchive();
        $filename = tempnam(sys_get_temp_dir(), 'zip');
        if ($zip->open($filename, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE) !== TRUE) {
            return abort(500);
        }

        // Querying posts
        $ids = explode(',', $ids);

        $categories = Category::ordered()->where('type', 'posts')->whereIn('id', $ids)->get(['id', 'name']);

        $posts = Post::where('is_published', true)->whereHas('categories', function (Builder $query) use ($ids) {
            $query->whereIn('id', $ids);
        })->with('categories')->get();


        // Add posts & their assets to zip file
        $content = '<!DOCTYPE html>
                    <html lang="ar" dir="rtl">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>الارشيف | الواقع 360</title>
                    </head>
                    <body>
                    <div style="max-width:800px;margin:0 auto">
                    <header>
                    <ul>
                    <li><span style="font-weight:bold">تم انشاء الملف بتاريخ:</span> <span dir="ltr">' . Carbon::now()->format('Y-m-d H:i:s') . '</span></li>
                    <li><span style="font-weight:bold">التصانيف المختارة:</span> <span dir="ltr">' . $categories->pluck('name')->join(', ') . '</span></li>
        <li><span style="font-weight:bold">الموقع:</span> <span dir="ltr">' . env('FRONTEND_URL') . '</span></li>

                  </ul>
                    </header>

                    <main>
                    ';

        $posts->each(function ($post) use (&$zip, &$content) {
            $mediaPath = $post->getFirstMediaPath('featured_image');
            $content .= '<article style="margin-top:2rem">
                        <h2>' . $post->title . '</h2>';
            if ($mediaPath) {
                $zip->addFile($mediaPath, "/images/" . basename($mediaPath));
                $content .= '
                            <figure>
  <img src="images/' . basename($mediaPath) . '" style="width:100%">
  <figcaption>التصانيف: ' . $post->categories->pluck('name')->join(', ') . '</figcaption>
</figure>
';
            }
            $content .= '
                        <div>' . $post->content . '</div>
                      </article>';
        });


        $content .= '</main></div></body></html>';

        $zip->addFromString('/index.html', $content);

        // Close zip file & download
        $zip->close();
        if (ob_get_contents()) ob_end_clean(); // If I don't do this, the output file would be corrupted.
        return response()->download($filename, 'export-' . Carbon::now()->format('Y-m-d') . '.zip');



        return $categories;
    }
}
