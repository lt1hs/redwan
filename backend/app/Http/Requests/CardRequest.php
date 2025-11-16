<?php

namespace App\Http\Requests;

use App\Models\Card;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isPersonal = $this->input('card_type') === Card::TYPE_PERSONAL;

        return [
            'full_name_fa' => ['required', 'string', 'max:255'],
            'father_name_fa' => ['required', 'string', 'max:255'],
            'full_name_en' => ['required', 'string', 'max:255'],
            'father_name_en' => ['required', 'string', 'max:255'],
            'passport_number' => ['required', 'string', 'max:255'],
            'national_id' => [$isPersonal ? 'required' : 'nullable', 'string', 'max:255'],
            'police_code' => [$isPersonal ? 'required' : 'nullable', 'string', 'max:255'],
            'card_expiry_date' => ['required', 'date'],
            'personal_photo' => ['nullable', 'string'],
            'passport_id' => ['nullable', 'exists:passports,id'],
            'card_type' => ['required', Rule::in([Card::TYPE_PERSONAL, Card::TYPE_WIFE, Card::TYPE_SON, Card::TYPE_DAUGHTER])],
            'parent_card_id' => ['nullable', 'exists:cards,id'],
            'status' => ['required', Rule::in([Card::STATUS_ACTIVE, Card::STATUS_EXPIRED, Card::STATUS_CANCELLED])],
            'citizenship_fa' => ['required', 'string', 'max:255'],
            'citizenship_en' => ['required', 'string', 'max:255'],
            'relative_relation' => [
                Rule::requiredIf(!$isPersonal),
                Rule::in([
                    Card::RELATION_IMMIGRANT_WIFE,
                    Card::RELATION_IMMIGRANT_SON,
                    Card::RELATION_IMMIGRANT_DAUGHTER
                ])
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name_fa.required' => 'نام و نام خانوادگی به فارسی الزامی است',
            'father_name_fa.required' => 'نام پدر به فارسی الزامی است',
            'full_name_en.required' => 'نام و نام خانوادگی به انگلیسی الزامی است',
            'father_name_en.required' => 'نام پدر به انگلیسی الزامی است',
            'passport_number.required' => 'شماره پاسپورت الزامی است',
            'national_id.required' => 'کد فراگیر الزامی است',
            'police_code.required' => 'کد پلیس الزامی است',
            'card_expiry_date.required' => 'تاریخ انقضای کارت الزامی است',
            'card_type.required' => 'نوع کارت الزامی است',
            'status.required' => 'وضعیت کارت الزامی است',
            'relative_relation.required' => 'The relative relation field is required for family members.',
            'relative_relation.in' => 'The selected relative relation is invalid.',
        ];
    }
} 