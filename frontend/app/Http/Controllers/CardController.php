protected function validateCard(Request $request)
{
    return $request->validate([
        'full_name_fa' => 'required|string|max:255',
        'full_name_en' => 'required|string|max:255',
        'father_name_fa' => 'required|string|max:255',
        'father_name_en' => 'required|string|max:255',
        'nationality_fa' => 'required|string|max:255',
        'nationality_en' => 'required|string|max:255',
        'citizenship_fa' => 'required|string|max:255',
        'citizenship_en' => 'required|string|max:255',
        'passport_number' => 'required|string|max:255',
        'national_id' => 'required|string|max:255',
        'police_code' => 'required|string|max:255',
        'bank_code' => 'required|string|max:255',
        'personal_photo' => 'nullable|image|max:2048',
        'status' => 'required|in:active,expired,pending,suspended'
    ]);
} 