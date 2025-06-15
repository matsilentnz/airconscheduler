<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Technician;
use Illuminate\Support\Facades\Hash;

class AdminTechnicianController extends Controller
{
    public function index()
    {
        $technicians = Technician::select('id','name', 'email')
            ->get();

        return response()->json($technicians);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:technicians,email',
            'password' => 'required|string|min:6',
        ]);

        $technician = Technician::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'message' => 'Technician registered successfully!',
            'technician' => [

                'name' => $technician->name,
                'email' => $technician->email,
            ]
        ], 201);
    }

    public function destroy($id)
    {
        $technician = Technician::findOrFail($id);
        $technician->delete();

        return response()->json([
            'message' => 'Technician deleted successfully'
        ]);
    }
}
