<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContractController extends Controller
{
    /**
     * Display a listing of the contracts.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $contracts = Contract::latest()->paginate(10);
        return response()->json($contracts);
    }

    /**
     * Store a newly created contract in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->merge($this->convertArabicNumbersToEnglish($request->all()));

        // Auto-generate contract number if not provided
        if (empty($request->contract_number)) {
            $request->merge(['contract_number' => $this->generateContractNumber()]);
        }

        $validated = $request->validate([
            'contract_number' => 'required|unique:contracts',
            'contract_date' => 'required|date',
            'contract_type' => 'nullable|string',
            'contract_place' => 'nullable|string',
            'husband_name' => 'required|string',
            'husband_nationality' => 'required|string',
            'husband_id_number' => 'nullable|string',
            'husband_birth_date' => 'nullable|string',
            'husband_phone' => 'nullable|string',
            'husband_address' => 'nullable|string',
            'husband_passport_number' => 'nullable|string',
            'wife_name' => 'required|string',
            'wife_nationality' => 'required|string',
            'wife_id_number' => 'nullable|string',
            'wife_birth_date' => 'nullable|string',
            'wife_phone' => 'nullable|string',
            'wife_address' => 'nullable|string',
            'wife_passport_number' => 'nullable|string',
            'present_dowry' => 'nullable|numeric',
            'deferred_dowry' => 'nullable|numeric',
            'husband_conditions_arabic' => 'nullable|string',
            'husband_conditions_persian' => 'nullable|string',
            'wife_conditions_arabic' => 'nullable|string',
            'wife_conditions_persian' => 'nullable|string',
            'first_witness' => 'nullable|string',
            'second_witness' => 'nullable|string',
            'officiant_name' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        $contract = Contract::create($validated);
        return response()->json(['data' => $contract], 201);
    }

    /**
     * Display the specified contract.
     *
     * @param  \App\Models\Contract  $contract
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Contract $contract)
    {
        return response()->json(['data' => $contract]);
    }

    /**
     * Update the specified contract in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Contract  $contract
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Contract $contract)
    {
        $request->merge($this->convertArabicNumbersToEnglish($request->all()));

        $validated = $request->validate([
            'contract_number' => ['required', Rule::unique('contracts')->ignore($contract->id)],
            'contract_date' => 'required|date',
            'contract_type' => 'nullable|string',
            'contract_place' => 'nullable|string',
            'husband_name' => 'required|string',
            'husband_nationality' => 'required|string',
            'husband_id_number' => 'nullable|string',
            'husband_birth_date' => 'nullable|string',
            'husband_phone' => 'nullable|string',
            'husband_address' => 'nullable|string',
            'husband_passport_number' => 'nullable|string',
            'wife_name' => 'required|string',
            'wife_nationality' => 'required|string',
            'wife_id_number' => 'nullable|string',
            'wife_birth_date' => 'nullable|string',
            'wife_phone' => 'nullable|string',
            'wife_address' => 'nullable|string',
            'wife_passport_number' => 'nullable|string',
            'present_dowry' => 'nullable|numeric',
            'deferred_dowry' => 'nullable|numeric',
            'husband_conditions_arabic' => 'nullable|string',
            'husband_conditions_persian' => 'nullable|string',
            'wife_conditions_arabic' => 'nullable|string',
            'wife_conditions_persian' => 'nullable|string',
            'first_witness' => 'nullable|string',
            'second_witness' => 'nullable|string',
            'officiant_name' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        $contract->update($validated);
        return response()->json(['data' => $contract]);
    }

    /**
     * Remove the specified contract from storage.
     *
     * @param  \App\Models\Contract  $contract
     * @return \Illuminate\Http\Response
     */
    public function destroy(Contract $contract)
    {
        $contract->delete();
        return response()->noContent();
    }

    /**
     * Converts Arabic numbers in an array to English numbers.
     *
     * @param array $data
     * @return array
     */
    private function convertArabicNumbersToEnglish(array $data): array
    {
        $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = str_replace($arabic, $english, $value);
            } elseif (is_array($value)) {
                $data[$key] = $this->convertArabicNumbersToEnglish($value);
            }
        }
        return $data;
    }

    /**
     * Generate a unique contract number.
     *
     * @return string
     */
    private function generateContractNumber(): string
    {
        $year = date('Y');
        $lastContract = Contract::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        
        $nextNumber = $lastContract ? (int)substr($lastContract->contract_number, -4) + 1 : 1;
        
        return $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
