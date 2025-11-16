<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $type)
    {
        return Category::where('type', $type)->ordered()->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $type)
    {
        Category::create([...$request->except('type'), "type" => $type]);
        return Category::where('type', $type)->ordered()->get();
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $type, Category $category)
    {
        $category->update($request->except('type'));
        return Category::where('type', $type)->ordered()->get();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($type, Category $category)
    {
        if ($category->$type()->exists())
            abort(422, 'لا يمكن حذف هذا القسم، لأنه يحتوي على مادة');

        $category->delete();
        return Category::where('type', $type)->ordered()->get();
    }

    public function setOrder(Request $request, $type)
    {
        Category::setNewOrder($request->ids);
        return Category::where('type', $type)->ordered()->get();
    }
}
