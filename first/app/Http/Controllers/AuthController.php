<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Mail\CustomerSignupMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\Technician;

class AuthController extends Controller
{
    // Register a new customer
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'password' => 'required|min:6',
            'phone' => 'required|string|max:15',
            'address' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            Log::info('Validation failed:', $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $customer = Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
        ]);


        try {
            Mail::to($customer->email)->send(new CustomerSignupMail($customer));
            Log::info('Email sent to customer: ' . $customer->email);
        } catch (\Exception $e) {
            Log::error('Mail sending failed: ' . $e->getMessage());
        }


        return response()->json([
            'message' => 'User registered successfully',
            'customer' => $customer->makeHidden(['password']),
        ], 201);
    }

    // Login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $customer = Customer::where('email', $request->email)->first();

        if (!$customer || !Hash::check($request->password, $customer->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Generate API token
        $token = $customer->createToken('customer_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'customer' => $customer->makeHidden(['password']),
        ]);
    }

    // Get authenticated user info
    public function profile(Request $request)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    return response()->json([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'phone' => $user->phone,
        'address' => $user->address,
    ]);
}
// Add this method to your AuthController.php
public function refreshToken(Request $request)
{
    $user = $request->user();

    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    // Revoke all tokens
    $user->tokens()->delete();

    // Create new token
    $token = $user->createToken('customer_token')->plainTextToken;

    return response()->json([
        'token' => $token,
        'message' => 'Token refreshed successfully'
    ]);
}


    // Logout
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    // Add these methods to your existing AuthController
public function technicianLogin(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    $technician = Technician::where('email', $request->email)->first();

    if (!$technician || !Hash::check($request->password, $technician->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $token = $technician->createToken('technician_token')->plainTextToken;

    return response()->json([
        'message' => 'Login successful',
        'token' => $token,
        'technician' => $technician->makeHidden(['password'])
    ]);
}

public function technicianLogout(Request $request)
{
    $request->user()->currentAccessToken()->delete();
    return response()->json(['message' => 'Logged out successfully']);
}
}
