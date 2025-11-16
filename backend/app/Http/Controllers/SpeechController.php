<?php

namespace App\Http\Controllers;

use App\Models\Speech;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SpeechController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Speech::query();

        // Apply search if provided
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('recipient', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $request->input('sortBy', 'created_at');
        $sortDesc = $request->input('sortDesc', 'true') === 'true';
        $query->orderBy($sortBy, $sortDesc ? 'desc' : 'asc');

        // Pagination
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);

        $total = $query->count();
        $speeches = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        return response()->json([
            'speeches' => $speeches,
            'total' => $total
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'recipient' => 'required|string|max:255',
            'content' => 'required|string',
            'paper_size' => 'required|string|in:A4,A3',
            'header_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'footer_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'signature_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->only([
            'title', 'recipient', 'content', 'paper_size'
        ]);

        // Handle file uploads
        if ($request->hasFile('header_image')) {
            $data['header_image'] = $request->file('header_image')->store('speeches/headers', 'public');
        }

        if ($request->hasFile('footer_image')) {
            $data['footer_image'] = $request->file('footer_image')->store('speeches/footers', 'public');
        }

        if ($request->hasFile('signature_image')) {
            $data['signature_image'] = $request->file('signature_image')->store('speeches/signatures', 'public');
        }

        $speech = Speech::create($data);

        return response()->json($speech, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Speech $speech)
    {
        return response()->json($speech);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Speech $speech)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'recipient' => 'required|string|max:255',
            'content' => 'required|string',
            'paper_size' => 'required|string|in:A4,A3',
            'header_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'footer_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'signature_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->only([
            'title', 'recipient', 'content', 'paper_size'
        ]);

        // Handle file uploads
        if ($request->hasFile('header_image')) {
            // Delete old file if exists
            if ($speech->header_image) {
                Storage::disk('public')->delete($speech->header_image);
            }
            $data['header_image'] = $request->file('header_image')->store('speeches/headers', 'public');
        }

        if ($request->hasFile('footer_image')) {
            // Delete old file if exists
            if ($speech->footer_image) {
                Storage::disk('public')->delete($speech->footer_image);
            }
            $data['footer_image'] = $request->file('footer_image')->store('speeches/footers', 'public');
        }

        if ($request->hasFile('signature_image')) {
            // Delete old file if exists
            if ($speech->signature_image) {
                Storage::disk('public')->delete($speech->signature_image);
            }
            $data['signature_image'] = $request->file('signature_image')->store('speeches/signatures', 'public');
        }

        $speech->update($data);

        return response()->json($speech);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Speech $speech)
    {
        // Delete associated files
        if ($speech->header_image) {
            Storage::disk('public')->delete($speech->header_image);
        }
        if ($speech->footer_image) {
            Storage::disk('public')->delete($speech->footer_image);
        }
        if ($speech->signature_image) {
            Storage::disk('public')->delete($speech->signature_image);
        }

        $speech->delete();

        return response()->json(null, 204);
    }
}<?php

namespace App\Http\Controllers;

use App\Models\Speech;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SpeechController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Speech::query();

        // Apply search if provided
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('recipient', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $request->input('sortBy', 'created_at');
        $sortDesc = $request->input('sortDesc', 'true') === 'true';
        $query->orderBy($sortBy, $sortDesc ? 'desc' : 'asc');

        // Pagination
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);

        $total = $query->count();
        $speeches = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        return response()->json([
            'speeches' => $speeches,
            'total' => $total
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'recipient' => 'required|string|max:255',
            'content' => 'required|string',
            'paper_size' => 'required|string|in:A4,A3',
            'header_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'footer_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'signature_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->only([
            'title', 'recipient', 'content', 'paper_size'
        ]);

        // Handle file uploads
        if ($request->hasFile('header_image')) {
            $data['header_image'] = $request->file('header_image')->store('speeches/headers', 'public');
        }

        if ($request->hasFile('footer_image')) {
            $data['footer_image'] = $request->file('footer_image')->store('speeches/footers', 'public');
        }

        if ($request->hasFile('signature_image')) {
            $data['signature_image'] = $request->file('signature_image')->store('speeches/signatures', 'public');
        }

        $speech = Speech::create($data);

        return response()->json($speech, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Speech $speech)
    {
        return response()->json($speech);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Speech $speech)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'recipient' => 'required|string|max:255',
            'content' => 'required|string',
            'paper_size' => 'required|string|in:A4,A3',
            'header_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'footer_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'signature_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->only([
            'title', 'recipient', 'content', 'paper_size'
        ]);

        // Handle file uploads
        if ($request->hasFile('header_image')) {
            // Delete old file if exists
            if ($speech->header_image) {
                Storage::disk('public')->delete($speech->header_image);
            }
            $data['header_image'] = $request->file('header_image')->store('speeches/headers', 'public');
        }

        if ($request->hasFile('footer_image')) {
            // Delete old file if exists
            if ($speech->footer_image) {
                Storage::disk('public')->delete($speech->footer_image);
            }
            $data['footer_image'] = $request->file('footer_image')->store('speeches/footers', 'public');
        }

        if ($request->hasFile('signature_image')) {
            // Delete old file if exists
            if ($speech->signature_image) {
                Storage::disk('public')->delete($speech->signature_image);
            }
            $data['signature_image'] = $request->file('signature_image')->store('speeches/signatures', 'public');
        }

        $speech->update($data);

        return response()->json($speech);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Speech $speech)
    {
        // Delete associated files
        if ($speech->header_image) {
            Storage::disk('public')->delete($speech->header_image);
        }
        if ($speech->footer_image) {
            Storage::disk('public')->delete($speech->footer_image);
        }
        if ($speech->signature_image) {
            Storage::disk('public')->delete($speech->signature_image);
        }

        $speech->delete();

        return response()->json(null, 204);
    }
}