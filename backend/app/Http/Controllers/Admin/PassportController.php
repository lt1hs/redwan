<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Passport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PassportController extends Controller
{
    public function index(Request $request)
    {
        $query = Passport::query();

        if ($request->has('passport_status')) {
            $status = $request->input('passport_status');
            if ($status === 'تم تسليمه') {
                $query->where('passport_status', 'تم تسليمه');
            } else {
                // If a status is provided but it's not 'تم تسليمه',
                // you might want to return an empty set or filter by the provided status.
                // For this task, we only care about 'تم تسليمه'.
                // For now, I'll make it filter by the provided status if it's not 'تم تسليمه'.
                $query->where('passport_status', $status);
            }
        }

        return $query->get();
    }

    public function store(Request $request)
    {
        // Detailed logging of the incoming request
        logger()->info('Passport creation request details', [
            'data' => $request->all(),
            'has_personal_photo' => $request->hasFile('personal_photo'),
            'has_passport_photo' => $request->hasFile('passport_photo'),
            'content_type' => $request->header('Content-Type'),
            'payment_status' => $request->input('payment_status')
        ]);

        // Validate the request
        $validated = $request->validate([
            'full_name' => 'required|string',
            'nationality' => 'required|string',
            'passport_number' => 'required|string|unique:passports',
            'date_of_birth' => 'required|date',
            'residence_expiry_date' => 'required|string',
            'phone_number' => 'required|string',
            'mobile_number' => 'required|string',
            'passport_status' => 'required|string',
            'passport_delivery_date' => 'required|date',
            'transaction_type' => 'required|string',
            'payment_status' => 'required|string|in:pending,paid,cancelled',
            'delivered_by' => 'required|string',
            'address' => 'required|string',
            'zipcode' => 'nullable|string',
            'personal_photo' => 'nullable|file|max:4096',
            'passport_photo' => 'nullable|file|max:4096',
            'unique_code' => 'nullable|string',
            'email' => 'nullable|email',
            'sponsor_name' => 'nullable|string',
            'relationship' => 'nullable|string',
            'extension_reason' => 'nullable|string',
            'barcode' => 'nullable|string',
            'signature_data' => 'nullable|string',
            'no_signature' => 'nullable|boolean',
            'no_signature_reason' => 'nullable|string'
        ]);

        // Handle file uploads
        if ($request->hasFile('personal_photo')) {
            $file = $request->file('personal_photo');
            logger()->info('Processing personal_photo', [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType()
            ]);
            $validated['personal_photo'] = $file->store('photos', 'public');
        }

        if ($request->hasFile('passport_photo')) {
            $file = $request->file('passport_photo');
            logger()->info('Processing passport_photo', [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType()
            ]);
            $validated['passport_photo'] = $file->store('photos', 'public');
        }

        // Create the passport
        $passport = Passport::create($validated);

        // Log the successful creation
        logger()->info('Passport created successfully', [
            'id' => $passport->id,
            'passport_number' => $passport->passport_number
        ]);

        return $passport;
    }

    public function show(Passport $passport)
    {
        return $passport;
    }

    public function update(Request $request, Passport $passport)
    {
        $validated = $request->validate([
            'full_name' => 'sometimes|string',
            'nationality' => 'sometimes|string',
            'passport_number' => 'sometimes|string',
            'date_of_birth' => 'sometimes|date',
            'residence_expiry_date' => 'sometimes|string',
            'phone_number' => 'sometimes|string',
            'mobile_number' => 'sometimes|string',
            'passport_status' => 'sometimes|string',
            'passport_delivery_date' => 'sometimes|date',
            'transaction_type' => 'sometimes|string',
            'payment_status' => 'sometimes|in:pending,paid,cancelled',
            'delivered_by' => 'sometimes|string',
            'address' => 'sometimes|string',
            'zipcode' => 'nullable|string',
            'personal_photo' => 'nullable|image|max:2048',
            'passport_photo' => 'nullable|image|max:2048',
            'unique_code' => 'nullable|string',
            'email' => 'nullable|email',
            'sponsor_name' => 'nullable|string',
            'relationship' => 'nullable|string',
            'extension_reason' => 'nullable|string',
            'barcode' => 'nullable|string',
            'signature_data' => 'nullable|string',
            'no_signature' => 'nullable|boolean',
            'no_signature_reason' => 'nullable|string'
        ]);

        if ($request->hasFile('personal_photo')) {
            $validated['personal_photo'] = $request->file('personal_photo')->store('photos', 'public');
        }

        if ($request->hasFile('passport_photo')) {
            $validated['passport_photo'] = $request->file('passport_photo')->store('photos', 'public');
        }

        $passport->update($validated);
        return $passport;
    }

    public function destroy(Passport $passport)
    {
        $passport->delete();
        return response()->noContent();
    }

    public function showPhoto($filename)
    {
        $path = 'photos/' . $filename;

        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'Image not found.');
        }

        return response()->file(Storage::disk('public')->path($path));
    }
}
