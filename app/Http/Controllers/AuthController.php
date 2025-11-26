<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // Step 1: Validate the incoming request data
        // This ensures email is unique, password meets requirements, etc.
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Step 2: Create the user
        // Hash::make() automatically hashes the password before storing
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Step 3: Return success response
        // HTTP 201 = Created (successful creation of resource)
        return response()->json([
            'message' => 'User registered successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 201);
    }

    /**
     * Login user and return API token
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Step 1: Validate email and password are provided
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Step 2: Find user by email
        $user = User::where('email', $request->email)->first();

        // Step 3: Check if user exists and password is correct
        // Hash::check() compares plain password with hashed password
        if (!$user || !Hash::check($request->password, $user->password)) {
            // Throw validation exception with 422 status code
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Step 4: Create Sanctum token for this user
        // This token will be stored in personal_access_tokens table
        // User can use this token in Authorization header for future requests
        $token = $user->createToken('auth-token')->plainTextToken;

        // Step 5: Return token and user info
        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 200);
    }
}
