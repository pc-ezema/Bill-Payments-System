<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test creating a transaction.
     *
     * @return void
     */
    public function test_create_transaction()
    {
        // Create a user for testing
        $user = User::factory()->create();

        // Prepare the transaction data
        $transactionData = [
            'user_id' => $user->id,
            'amount' => 100,
            'status' => 'pending',
            'description' => 'Payment received'
        ];

        // Send POST request to create transaction
        $response = $this->postJson('/api/v1/transactions', $transactionData);

        // Assert that the response status is 201 (created)
        $response->assertStatus(201);

        // Assert that the response contains the correct data
        $response->assertJsonFragment([
            'message' => 'Transaction created successfully.',
        ]);

        // Assert that the transaction is stored in the database
        $this->assertDatabaseHas('transactions', $transactionData);
    }

    /**
     * Test retrieving a transaction.
     *
     * @return void
     */
    public function test_get_transaction()
    {
        // Create a user for testing
        $user = User::factory()->create();

        // Create a transaction for the user
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'amount' => 100,
            'status' => 'pending',
            'description' => 'This is description'
        ]);

        // Send GET request to retrieve the transaction
        $response = $this->getJson('/api/v1/transactions/' . $transaction->id);

        // Assert that the response is successful
        $response->assertStatus(200);

        // Assert that the response contains the correct message
        $response->assertJsonFragment([
            'message' => 'Transaction retrieved successfully.',
        ]);

        // Assert that the response contains the correct transaction data
        $response->assertJsonFragment([
            'id' => $transaction->id,
            'user_id' => $transaction->user_id,
            'amount' => number_format($transaction->amount, 2, '.', ''), // Format amount to match the returned value
            'status' => $transaction->status,
            'description' => $transaction->description,
        ]);

        // Assert that only one transaction is returned (since we are no longer returning an array)
        $response->assertJsonMissing(['data' => []]);  // Ensure no array is returned
    }

    /**
     * Test updating a transaction.
     *
     * @return void
     */
    public function test_update_transaction()
    {
        // Create a user for testing
        $user = User::factory()->create();

        // Create a transaction for the user
        $transaction = Transaction::factory()->create(['user_id' => $user->id, 'amount' => 100, 'status' => 'pending', 'description' => 'Payment received']);

        // Prepare updated transaction data
        $updatedData = [
            'id' => $transaction->id,
            'amount' => 150,
            'status' => 'pending',
            'description' => 'Refund processed'
        ];

        // Send PUT request to update the transaction
        $response = $this->putJson('/api/v1/transactions/', $updatedData);

        // Assert that the response status is 200 (OK)
        $response->assertStatus(200);

        // Assert that the response contains the correct updated data
        $response->assertJsonFragment([
            'message' => 'Transaction updated successfully.',
        ]);

        // Assert that the transaction is updated in the database
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'amount' => 150,
            'status' => 'pending',
            'description' => 'Refund processed'
        ]);
    }

    /**
     * Test deleting a transaction.
     *
     * @return void
     */
    public function test_delete_transaction()
    {
        // Create a user for testing
        $user = User::factory()->create();

        // Create a transaction for the user
        $transaction = Transaction::factory()->create(['user_id' => $user->id, 'amount' => 100, 'status' => 'pending', 'description' => 'Payment received']);

        // Send DELETE request to delete the transaction
        $response = $this->deleteJson('/api/v1/transactions/' . $transaction->id);

        // Assert that the response status is 200 (OK)
        $response->assertStatus(200);

        // Assert that the response contains the correct message
        $response->assertJsonFragment([
            'message' => 'Transaction deleted successfully.'
        ]);

        // Assert that the transaction is removed from the database
        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    }
}
