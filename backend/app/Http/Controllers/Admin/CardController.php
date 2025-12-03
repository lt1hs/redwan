<?php

namespace App\Http\Controllers\Admin;

use App\Models\Card;
use App\Models\Passport;
use App\Models\ActivityLog;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class CardController extends Controller
{
    public function index(Request $request)
    {
        $query = Card::with(['passport', 'familyCards'])
            ->latest();

        $perPage = $request->input('per_page', 10);
        if ($perPage === 'all') {
            $cards = $query->get();
            // Add index to each card
            $cards->each(function ($card, $index) {
                $card->index = $index + 1;
            });
            return response()->json(['data' => $cards]);
        }

        $cards = $query->paginate((int)$perPage);
        // Add index to each card based on pagination
        $cards->getCollection()->each(function ($card, $index) use ($cards) {
            $card->index = (($cards->currentPage() - 1) * $cards->perPage()) + $index + 1;
        });
        return response()->json($cards);
    }

    /**
     * Get all cards including family members
     */
    public function getAllCards()
    {
        $cards = Card::with(['passport', 'parentCard'])
            ->latest()
            ->get();

        // Add a flag to identify family members
        $cards->each(function ($card) {
            $card->is_family_member = !is_null($card->parent_card_id);
            if ($card->is_family_member && $card->parentCard) {
                $card->parent_name = $card->parentCard->full_name_fa;
            }
        });

        return response()->json($cards);
    }

    public function store(Request $request)
    {
        try {
            // Log the request data for debugging
            \Log::info('Card creation request data:', $request->all());

            // Filter out potentially problematic fields
            $requestData = $request->all();
            $fieldsToRemove = ['unique_code', 'user_id', 'created_at', 'updated_at', 'id'];
            foreach ($fieldsToRemove as $field) {
                if (isset($requestData[$field])) {
                    unset($requestData[$field]);
                }
            }

            // For family members, set a default value for national_id to handle the NOT NULL constraint
            if (isset($requestData['card_type']) && $requestData['card_type'] !== 'personal' && empty($requestData['national_id'])) {
                $requestData['national_id'] = 'FAMILY-' . time();
                \Log::info('Set default national_id for family member:', ['national_id' => $requestData['national_id']]);
            }

            // Validate the request data
            $validator = Validator::make($requestData, [
                'full_name_fa' => 'required|string|max:255',
                'full_name_en' => 'required|string|max:255',
                'father_name_fa' => 'required|string|max:255',
                'father_name_en' => 'required|string|max:255',
                'passport_number' => 'required|string|max:255',
                'police_code' => 'required|string|max:255',
                'card_expiry_date' => 'required|date',
                'status' => 'required|string|in:active,expired,cancelled',
                'citizenship_en' => 'required|string|max:255',
                'citizenship_fa' => 'required|string|max:255',
                'card_type' => 'required|string|in:personal,wife,son,daughter',
                'personal_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'passport_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'parent_card_id' => 'nullable|exists:cards,id',
                'national_id' => 'required|string|max:255'
            ]);

            if ($validator->fails()) {
                \Log::error('Validation failed:', $validator->errors()->toArray());
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get a list of fillable columns from the Card model
            $fillableColumns = (new Card())->getFillable();
            
            // Only keep the data that matches the fillable columns
            $filteredData = array_intersect_key($requestData, array_flip($fillableColumns));
            
            // Create the card with filtered data
            $card = new Card();
            foreach ($filteredData as $key => $value) {
                $card->$key = $value;
            }
            $card->save();

            // Log activity for card creation
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'module' => 'cards',
                'description' => 'Created card: ' . $card->full_name_fa . ' (ID: ' . $card->id . ')',
            ]);

            // Handle file upload
            if ($request->hasFile('personal_photo')) {
                try {
                    $path = $request->file('personal_photo')->store('photos/cards', 'public');
                    $card->personal_photo = $path;
                    $card->save();
                } catch (\Exception $e) {
                    \Log::error('Error uploading personal photo:', [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            if ($request->hasFile('passport_photo')) {
                try {
                    $path = $request->file('passport_photo')->store('photos/cards', 'public');
                    $card->passport_photo = $path;
                    $card->save();
                } catch (\Exception $e) {
                    \Log::error('Error uploading passport photo:', [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            return response()->json([
                'message' => 'Card created successfully',
                'data' => $card
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error creating card:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'message' => 'Error creating card',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Card $card)
    {
        return response()->json($card->load(['passport', 'familyCards']));
    }

    public function update(Request $request, Card $card)
    {
        try {
            $isPersonalCard = $request->input('card_type') === Card::TYPE_PERSONAL;
            
            $validationRules = [
                'full_name_fa' => 'required|string',
                'father_name_fa' => 'required|string',
                'full_name_en' => 'required|string',
                'father_name_en' => 'required|string',
                'passport_number' => 'required|string',
                'police_code' => 'required|string',
                'card_expiry_date' => 'required|date',
                'citizenship_fa' => 'required|string',
                'citizenship_en' => 'required|string',
                'personal_photo' => 'nullable|image|max:2048',
                'passport_photo' => 'nullable|image|max:2048',
                'passport_id' => 'nullable|exists:passports,id',
                'card_type' => ['required', Rule::in([
                    Card::TYPE_PERSONAL,
                    Card::TYPE_WIFE,
                    Card::TYPE_SON,
                    Card::TYPE_DAUGHTER
                ])],
                'parent_card_id' => 'nullable|exists:cards,id',
                'status' => ['required', Rule::in([
                    Card::STATUS_ACTIVE,
                    Card::STATUS_EXPIRED,
                    Card::STATUS_CANCELLED
                ])],
                'unique_code' => 'nullable|string',
                'national_id' => 'required|string'
            ];

            // Only validate the detailed national_id requirements for personal cards
            if ($isPersonalCard) {
                $validationRules['national_id'] = 'required|string';
            } else {
                // For family cards, we just need something in the field, but we'll generate it if missing
                if (!$request->has('national_id') || empty($request->input('national_id'))) {
                    $request->merge(['national_id' => 'FAMILY-' . time()]);
                }
            }

            $validated = $request->validate($validationRules);

            // Format date
            $validated['card_expiry_date'] = Carbon::parse($validated['card_expiry_date'])->format('Y-m-d');

            // Handle photo upload
            if ($request->hasFile('personal_photo')) {
                if ($card->personal_photo) {
                    Storage::disk('public')->delete($card->personal_photo);
                }
                $validated['personal_photo'] = $request->file('personal_photo')->store('photos/cards', 'public');
            }

            if ($request->hasFile('passport_photo')) {
                if ($card->passport_photo) {
                    Storage::disk('public')->delete($card->passport_photo);
                }
                $validated['passport_photo'] = $request->file('passport_photo')->store('photos/cards', 'public');
            }

            $card->update($validated);

            // Log activity for card update
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'module' => 'cards',
                'description' => 'Updated card: ' . $card->full_name_fa . ' (ID: ' . $card->id . ')',
            ]);

            return response()->json([
                'message' => 'Card updated successfully',
                'data' => $card->fresh()->load(['passport', 'familyCards'])
            ]);
        } catch (\Exception $e) {
            \Log::error('Card update error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error updating card',
                'errors' => $e->getMessage()
            ], 422);
        }
    }

    public function destroy(Card $card)
    {
        try {
            if ($card->personal_photo) {
                Storage::disk('public')->delete($card->personal_photo);
            }

            // If this is a main card, also delete all family cards
            if (!$card->parent_card_id) {
                foreach ($card->familyCards as $familyCard) {
                    if ($familyCard->personal_photo) {
                        Storage::disk('public')->delete($familyCard->personal_photo);
                    }
                    $familyCard->delete();
                }
            }

            $card->delete();

            // Log activity for card deletion
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'module' => 'cards',
                'description' => 'Deleted card: ' . $card->full_name_fa . ' (ID: ' . $card->id . ')',
            ]);

            return response()->json([
                'message' => 'Card deleted successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Card deletion error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error deleting card',
                'errors' => $e->getMessage()
            ], 422);
        }
    }

    public function showPhoto($filename)
    {
        $path = 'photos/cards/' . $filename;

        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'Image not found.');
        }

        return response()->file(Storage::disk('public')->path($path));
    }

    public function getFamilyCards(Card $card)
    {
        if ($card->card_type !== Card::TYPE_PERSONAL) {
            return response()->json([
                'message' => 'Only personal cards can have family members'
            ], 422);
        }

        return response()->json($card->familyCards);
    }

    public function addFamilyMember(Request $request, Card $card)
    {
        if ($card->card_type !== Card::TYPE_PERSONAL) {
            return response()->json([
                'message' => 'Only personal cards can have family members'
            ], 422);
        }

        try {
            $request->merge(['parent_card_id' => $card->id]);
            return $this->store($request);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error adding family member',
                'errors' => $e->getMessage()
            ], 422);
        }
    }

    protected function getValidationRules($isUpdate = false): array
    {
        $rules = [
            'full_name_fa' => 'required|string|max:255',
            'full_name_en' => 'required|string|max:255',
            'father_name_fa' => 'required|string|max:255',
            'father_name_en' => 'required|string|max:255',
            'passport_number' => 'required|string|max:255',
            'police_code' => 'required|string|max:255',
            'card_expiry_date' => 'required|date',
            'status' => 'required|in:active,inactive',
            'citizenship_en' => 'required|string|max:255',
            'citizenship_fa' => 'required|string|max:255',
            'card_type' => 'required|string|in:personal,son,daughter,wife,husband',
            'personal_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'passport_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ];

        // Add parent_card_id validation for family member cards
        if (request()->input('card_type') !== 'personal') {
            $rules['parent_card_id'] = 'required|exists:cards,id';
        } else {
            $rules['national_id'] = 'required|string|max:255';
        }

        return $rules;
    }
}
