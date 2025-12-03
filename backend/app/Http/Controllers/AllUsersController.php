<?php

namespace App\Http\Controllers;

use App\Models\Passport;
use App\Models\Card;
use App\Models\UnfinishedPassport;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AllUsersController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);
        
        // Collect all users from different modules
        $users = collect();
        
        // From passports
        $passports = Passport::select('id', 'full_name as name', 'mobile_number as phone', 'created_at')
            ->selectRaw("'passport' as source")
            ->whereNotNull('mobile_number')
            ->where('mobile_number', '!=', '')
            ->get();
        $users = $users->merge($passports);
        
        // From cards (using passport_number as identifier since no phone)
        $cards = Card::select('id', 'full_name_fa as name', 'passport_number as phone', 'created_at')
            ->selectRaw("'card' as source")
            ->whereNotNull('passport_number')
            ->where('passport_number', '!=', '')
            ->get();
        $users = $users->merge($cards);
        
        // From unfinished passports
        $unfinishedPassports = UnfinishedPassport::select('id', 'full_name as name', 'mobile_number as phone', 'created_at')
            ->selectRaw("'unfinished_passport' as source")
            ->whereNotNull('mobile_number')
            ->where('mobile_number', '!=', '')
            ->get();
        $users = $users->merge($unfinishedPassports);
        
        // From contracts (using husband info as primary)
        $contracts = Contract::select('id', 'husband_name as name', 'husband_phone as phone', 'created_at')
            ->selectRaw("'contract' as source")
            ->whereNotNull('husband_phone')
            ->where('husband_phone', '!=', '')
            ->get();
        $users = $users->merge($contracts);
        
        // Remove duplicates based on phone number
        $users = $users->unique('phone')->values();
        
        // Sort by created_at desc
        $users = $users->sortByDesc('created_at')->values();
        
        // Manual pagination
        $total = $users->count();
        
        if ($perPage === 'all') {
            $paginatedUsers = $users;
            $lastPage = 1;
            $offset = 0;
        } else {
            $offset = ($page - 1) * $perPage;
            $paginatedUsers = $users->slice($offset, $perPage)->values();
            $lastPage = $total > 0 ? ceil($total / $perPage) : 1;
        }
        
        // Add index to each user
        $paginatedUsers = $paginatedUsers->map(function ($user, $index) use ($offset, $perPage) {
            $user->index = $perPage === 'all' ? $index + 1 : $offset + $index + 1;
            return $user;
        });
        
        return response()->json([
            'data' => $paginatedUsers,
            'total' => $total,
            'current_page' => (int) $page,
            'last_page' => $lastPage,
            'per_page' => $perPage
        ]);
    }
}
