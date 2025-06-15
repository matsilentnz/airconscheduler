<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{

    public function getAllCustomers()
    {
        try {
            $customers = Customer::select('id', 'name', 'email', 'phone', 'address')
                ->orderBy('id', 'asc')
                ->get();

            return response()->json($customers);
        } catch (\Exception $e) {
            Log::error('Admin error fetching customers: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch customers: ' . $e->getMessage()
            ], 500);
        }
    }
}
