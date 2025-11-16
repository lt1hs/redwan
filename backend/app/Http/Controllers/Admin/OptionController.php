<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OptionController extends Controller
{

    public function index()
    {
        return Option::all()->pluck('value', 'key')->toJson(options: JSON_FORCE_OBJECT);
        // return $result->toJson(JSON_FORCE_OBJECT);
    }

    public function update(Request $request)
    {
        return DB::transaction(function () use ($request) {
            foreach ($request->all() as $key => $value)
                Option::updateOrCreate(['key' => $key], ['value' => $value]);
        });
    }
}
