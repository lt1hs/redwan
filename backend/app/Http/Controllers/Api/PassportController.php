<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Passport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PassportController extends Controller
{
    // Payment status translation maps
    protected $paymentStatusToArabic = [
        'pending' => 'في انتظار الدفع',
        'paid' => 'تم الدفع',
        'cancelled' => 'ملغي'
    ];

    protected $arabicToPaymentStatus = [
        'في انتظار الدفع' => 'pending',
        'تم الدفع' => 'paid',
        'ملغي' => 'cancelled',
        // Legacy values for backward compatibility
        'تم' => 'paid',
        'لم يتم' => 'pending',
        'يدفع لاحقا' => 'pending',
        'قيد الانجاز' => 'pending',
        'جاهز للاستلام' => 'pending',
        'تم تسليمه' => 'paid',
    ];

    public function index()
    {
        $passports = Passport::latest()->get();
        
        // Translate payment status to Arabic for frontend
        foreach ($passports as $passport) {
            $passport->payment_status_ar = $this->paymentStatusToArabic[$passport->payment_status] ?? 'في انتظار الدفع';
        }
        
        return response()->json($passports);
    }

    public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'full_name' => 'required|string',
            'nationality' => 'required|string',
            'passport_number' => 'required|string',
            'date_of_birth' => 'required|date',
            'residence_expiry_date' => 'required|string',
            'phone_number' => 'required|string',
            'mobile_number' => 'required|string',
            'passport_status' => 'required|string',
            'passport_delivery_date' => 'required|date',
            'transaction_type' => 'required|string',
            'payment_status' => 'required|string',
            'delivered_by' => 'required|string',
            'address' => 'required|string',
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

        // Process payment status to backend format
        $validated['payment_status'] = $this->arabicToPaymentStatus[$validated['payment_status']] ?? 'pending';

        // Generate unique code if not provided
        if (empty($validated['unique_code'])) {
            $validated['unique_code'] = Str::random(10);
        }

        // Handle file uploads
        if ($request->hasFile('personal_photo')) {
            $validated['personal_photo'] = $request->file('personal_photo')->store('photos/personal', 'public');
        }

        if ($request->hasFile('passport_photo')) {
            $validated['passport_photo'] = $request->file('passport_photo')->store('photos/passport', 'public');
        }

        $passport = Passport::create($validated);

        // Add Arabic translation for frontend
        $passport->payment_status_ar = $this->paymentStatusToArabic[$passport->payment_status] ?? 'في انتظار الدفع';
        
        return response()->json($passport, 201);
    }

    public function show($id)
    {
        $passport = Passport::findOrFail($id);
        
        // Add Arabic translation for frontend
        $passport->payment_status_ar = $this->paymentStatusToArabic[$passport->payment_status] ?? 'في انتظار الدفع';
        
        return response()->json($passport);
    }

    public function update(Request $request, $id)
    {
        $passport = Passport::findOrFail($id);
        
        // Validate the request data
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
            'payment_status' => 'sometimes|string',
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

        // Process payment status to backend format if provided
        if (isset($validated['payment_status'])) {
            $validated['payment_status'] = $this->arabicToPaymentStatus[$validated['payment_status']] ?? 'pending';
        }

        // Handle file uploads
        if ($request->hasFile('personal_photo')) {
            // Delete old photo if exists
            if ($passport->personal_photo && Storage::disk('public')->exists($passport->personal_photo)) {
                Storage::disk('public')->delete($passport->personal_photo);
            }
            $validated['personal_photo'] = $request->file('personal_photo')->store('photos/personal', 'public');
        }

        if ($request->hasFile('passport_photo')) {
            // Delete old photo if exists
            if ($passport->passport_photo && Storage::disk('public')->exists($passport->passport_photo)) {
                Storage::disk('public')->delete($passport->passport_photo);
            }
            $validated['passport_photo'] = $request->file('passport_photo')->store('photos/passport', 'public');
        }

        $passport->update($validated);
        
        // Add Arabic translation for frontend
        $passport->payment_status_ar = $this->paymentStatusToArabic[$passport->payment_status] ?? 'في انتظار الدفع';
        
        return response()->json($passport);
    }

    public function destroy($id)
    {
        $passport = Passport::findOrFail($id);
        
        // Delete associated photos
        if ($passport->personal_photo && Storage::disk('public')->exists($passport->personal_photo)) {
            Storage::disk('public')->delete($passport->personal_photo);
        }
        
        if ($passport->passport_photo && Storage::disk('public')->exists($passport->passport_photo)) {
            Storage::disk('public')->delete($passport->passport_photo);
        }
        
        $passport->delete();
        
        return response()->json(null, 204);
    }
}
