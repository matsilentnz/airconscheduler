<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    // Get authenticated customer's details
    public function getCustomer(Request $request)
    {
        Log::info('Customer API called', ['user_id' => Auth::id()]);

        $user = Auth::user(); // Get authenticated user

        if (!$user) {
            Log::warning('No authenticated user found');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Safely log user data without using toArray()
        Log::info('Returning customer data', [
            'customer_id' => $user->id,
            'customer_email' => $user->email
        ]);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $user->address,
        ]);
    }

    // Update customer information
    // Update customer information
public function updateCustomer(Request $request)
{
    Log::info('Update customer API called', ['user_id' => Auth::id()]);

    $userId = Auth::id();

    if (!$userId) {
        Log::warning('No authenticated user found during update');
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    // Get the customer record directly from the Customer model
    $customer = Customer::find($userId);

    if (!$customer) {
        Log::warning('Customer not found with ID: ' . $userId);
        return response()->json(['message' => 'Customer not found'], 404);
    }

    // Validate the request data
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:customers,email,'.$customer->id,
        'phone' => 'required|string|max:15',
        'address' => 'required|string|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    try {
        // Update the customer record
        $customer->name = $request->name;
        $customer->email = $request->email;
        $customer->phone = $request->phone;
        $customer->address = $request->address;
        $customer->save();

        Log::info('Customer updated successfully', [
            'customer_id' => $customer->id,
            'customer_email' => $customer->email
        ]);

        // Return the updated customer data
        return response()->json([
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'address' => $customer->address,
            'message' => 'Profile updated successfully'
        ]);

    } catch (\Exception $e) {
        Log::error('Error updating customer', [
            'customer_id' => $customer->id,
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'message' => 'Failed to update profile. Please try again.'
        ], 500);
    }
}

}
