<?php

namespace App\Dijlah;

use Illuminate\Http\Request;

trait UpdateOrDeleteMedia
{
    public function updateOrDeleteMediaFromBase64Request(Request $request, string $collectionName, string $diskName = '')
    {
        if ($request->filled($collectionName)) {
            $this->addMediaFromBase64($request->input($collectionName))->usingFileName(uniqid() . '.jpg')->toMediaCollection($collectionName, $diskName);
        } elseif (!$request->filled($collectionName . '_url')) {
            $this->clearMediaCollection($collectionName);
        }
    }
}
