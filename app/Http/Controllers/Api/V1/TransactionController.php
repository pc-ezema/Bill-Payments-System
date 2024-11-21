<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\TransactionResource;
use App\Http\Resources\V1\UserResource;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transactions = Transaction::with('user')->get();

        return response()->json([
            'message' => 'All transactions retrieved successfully.',
            'data' => TransactionResource::collection($transactions)
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
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
                'user_id' => 'required|exists:users,id',
                'amount' => 'required|numeric|min:0',
                'status' => 'required|string|in:pending,completed,failed',
                'description' => 'nullable|string|max:255',
            ]);

            $transaction = Transaction::create($data);

            return response()->json([
                'message' => 'Transaction created successfully.',
                'data' => new TransactionResource($transaction)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred during the creation of ttransaction.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $transaction = Transaction::findOrFail($id);

            return response()->json([
                'message' => 'Transaction retrieved successfully.',
                'data' => new TransactionResource($transaction)
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Transaction not found.'
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
                'id' => 'required|exists:transactions,id', // Validate ID exists in transactions table
                'amount' => 'nullable|numeric|min:0',
                'status' => 'nullable|string|in:pending,completed,failed',
                'description' => 'nullable|string|max:255'
            ]);

            // Find the transaction by ID
            $transaction = Transaction::findOrFail($data['id']);

            // Update the transaction
            $transaction->update([
                'amount' => $data['amount'],
                'status' => $data['status'],
                'description' => $data['description']
            ]);

            // Return success response
            return response()->json([
                'message' => 'Transaction updated successfully.',
                'data' => new TransactionResource($transaction)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Transaction not found.'
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
    public function destroy(string $id)
    {
        try {
            // Find the transaction by ID
            $transaction = Transaction::findOrFail($id);

            $transaction->delete();

            return response()->json([
                'message' => 'Transaction deleted successfully.',
                'data' => [
                    'id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'status' => $transaction->status,
                ]
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Transaction not found.'
            ], 404);
        }
    }
}
