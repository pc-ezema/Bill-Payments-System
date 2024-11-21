<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Load users with transaction count
        $users = User::withCount('transactions')->get();

        return response()->json([
            'message' => 'All users',
            'data' => UserResource::collection($users)
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:8',
            ]);

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
            ]);

            return response()->json([
                'message' => 'All users',
                'data' => new UserResource($user)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred during the creation of user.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $user = User::findOrFail($id)->load('transactions');

            return response()->json([
                'message' => 'User details retrieved successfully.',
                'data' => new UserResource($user)
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        try {
            $data = $request->validate([
                'id' => 'required|exists:users,id', // Validate ID exists in users table
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $request->id,
                'password' => 'nullable|string|min:8',
            ]);

            // Find the user by ID
            $user = User::findOrFail($data['id']);

            // Update the user
            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'] ? bcrypt($data['password']) : $user->password,
            ]);

            // Return success response
            return response()->json([
                'message' => 'User updated successfully.',
                'data' => new UserResource($user)
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred during the update.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            // Find the user by ID
            $user = User::findOrFail($id);

            // Delete associated transactions
            $user->transactions()->delete();  // Delete the transactions associated with the user

            $user->delete();

            return response()->json([
                'message' => 'User details retrieved successfully, and the associated transactions have been deleted.',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }
    }
}
