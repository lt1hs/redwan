<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name_fa' => $this->full_name_fa,
            'father_name_fa' => $this->father_name_fa,
            'full_name_en' => $this->full_name_en,
            'father_name_en' => $this->father_name_en,
            'passport_number' => $this->passport_number,
            'national_id' => $this->national_id,
            'police_code' => $this->police_code,
            'card_expiry_date' => $this->card_expiry_date?->format('Y-m-d'),
            'personal_photo' => $this->personal_photo ? asset('storage/' . $this->personal_photo) : null,
            'card_type' => $this->card_type,
            'status' => $this->status,
            'passport' => new PassportResource($this->whenLoaded('passport')),
            'parent_card' => new CardResource($this->whenLoaded('parentCard')),
            'dependent_cards' => CardResource::collection($this->whenLoaded('dependentCards')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
} 