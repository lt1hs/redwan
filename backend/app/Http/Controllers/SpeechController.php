<?php

namespace App\Http\Controllers;

use App\Models\Speech;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SpeechController extends Controller
{
    public function index(Request $request)
    {
        $query = Speech::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('recipient', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->has('template_type')) {
            $query->where('template_type', $request->template_type);
        }

        if ($request->has('paper_size')) {
            $query->where('paper_size', $request->paper_size);
        }

        $sortBy = $request->input('sortBy', 'created_at');
        $sortDesc = $request->input('sortDesc', 'true') === 'true';
        $query->orderBy($sortBy, $sortDesc ? 'desc' : 'asc');

        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);

        $total = $query->count();
        $speeches = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        return response()->json([
            'speeches' => $speeches,
            'total' => $total,
            'current_page' => $page,
            'per_page' => $perPage,
            'last_page' => ceil($total / $perPage)
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'recipient' => 'required|string|max:255',
            'content' => 'required|string',
            'paper_size' => 'required|string|in:A4,A3',
            'template_type' => 'nullable|string|in:official,invitation,thanks',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $speech = Speech::create($request->only([
            'title', 'recipient', 'content', 'paper_size', 'template_type'
        ]));

        return response()->json($speech, 201);
    }

    public function show(Speech $speech)
    {
        return response()->json($speech);
    }

    public function update(Request $request, Speech $speech)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'recipient' => 'required|string|max:255',
            'content' => 'required|string',
            'paper_size' => 'required|string|in:A4,A3',
            'template_type' => 'nullable|string|in:official,invitation,thanks',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $speech->update($request->only([
            'title', 'recipient', 'content', 'paper_size', 'template_type'
        ]));

        return response()->json($speech);
    }

    public function destroy(Speech $speech)
    {
        $speech->delete();
        return response()->json(null, 204);
    }

    public function duplicate(Speech $speech)
    {
        $newSpeech = $speech->replicate();
        $newSpeech->title = $speech->title . ' (نسخة)';
        $newSpeech->save();

        return response()->json($newSpeech, 201);
    }

    public function getTemplates()
    {
        return response()->json(Speech::getTemplates());
    }
}