<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CardRequest;
use App\Http\Resources\CardResource;
use App\Models\Card;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Request;

class CardController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $cards = Card::with(['passport', 'parentCard', 'dependentCards'])
            ->latest()
            ->paginate(10);

        return CardResource::collection($cards);
    }

    public function store(CardRequest $request): JsonResponse
    {
        $card = Card::create($request->validated());

        if ($request->hasFile('personal_photo')) {
            $path = $request->file('personal_photo')->store('cards/photos', 'public');
            $card->update(['personal_photo' => $path]);
        }

        return response()->json([
            'message' => 'Card created successfully',
            'data' => new CardResource($card->load(['passport', 'parentCard', 'dependentCards']))
        ], 201);
    }

    public function show(Card $card): CardResource
    {
        return new CardResource($card->load(['passport', 'parentCard', 'dependentCards']));
    }

    public function update(CardRequest $request, Card $card): JsonResponse
    {
        $card->update($request->validated());

        if ($request->hasFile('personal_photo')) {
            $path = $request->file('personal_photo')->store('cards/photos', 'public');
            $card->update(['personal_photo' => $path]);
        }

        return response()->json([
            'message' => 'Card updated successfully',
            'data' => new CardResource($card->load(['passport', 'parentCard', 'dependentCards']))
        ]);
    }

    public function destroy(Card $card): JsonResponse
    {
        $card->delete();

        return response()->json([
            'message' => 'Card deleted successfully'
        ]);
    }

    public function search(Request $request): AnonymousResourceCollection
    {
        $query = Card::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('full_name_fa', 'like', "%{$search}%")
                    ->orWhere('full_name_en', 'like', "%{$search}%")
                    ->orWhere('passport_number', 'like', "%{$search}%")
                    ->orWhere('national_id', 'like', "%{$search}%")
                    ->orWhere('police_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('card_type')) {
            $query->where('card_type', $request->input('card_type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $cards = $query->with(['passport', 'parentCard', 'dependentCards'])
            ->latest()
            ->paginate(10);

        return CardResource::collection($cards);
    }

    /**
     * Get family cards for a specific card
     */
    public function getFamilyCards(Card $card): AnonymousResourceCollection
    {
        $familyCards = $card->familyCards()->with(['passport'])->get();
        return CardResource::collection($familyCards);
    }

    /**
     * Add a family member card
     */
    public function addFamilyMember(CardRequest $request, Card $card): JsonResponse
    {
        if ($card->card_type !== Card::TYPE_PERSONAL) {
            return response()->json([
                'message' => 'Only personal cards can have family members'
            ], 422);
        }

        $familyMember = Card::create([
            ...$request->validated(),
            'parent_card_id' => $card->id,
        ]);

        if ($request->hasFile('personal_photo')) {
            $path = $request->file('personal_photo')->store('cards/photos', 'public');
            $familyMember->update(['personal_photo' => $path]);
        }

        return response()->json([
            'message' => 'Family member added successfully',
            'data' => new CardResource($familyMember->load(['passport']))
        ], 201);
    }
} 