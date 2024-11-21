<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the index method retrieves all users.
     *
     * @return void
     */
    public function test_index_users()
    {
        // Create users to test
        User::factory(3)->create(); // Create 3 users

        // Send a GET request to the 'index' method for the users
        $response = $this->json('GET', '/api/v1/users');

        // Assert that the response has status 200
        $response->assertStatus(200);

        // Assert that the response contains the correct message
        $response->assertJson([
            'message' => 'All users',
        ]);

        // Assert that the response contains the data of 3 users
        $response->assertJsonCount(3, 'data');
    }

    /**
     * Test that the update method successfully updates user data.
     *
     * @return void
     */
    public function test_update_user()
    {
        // Create a user to update
        $user = User::factory()->create();

        // Data to update the user
        $data = [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated.email@example.com',
            "password" => "newpassword123"
        ];

        // Send a PUT request to update the user
        $response = $this->json('PUT', "/api/v1/users", $data);

        // Assert that the response has status 200
        $response->assertStatus(200);

        // Assert that the response contains the correct message
        $response->assertJson([
            'message' => 'User updated successfully.',
        ]);

        // Assert that the user data is updated
        $response->assertJsonFragment([
            'name' => 'Updated Name',
            'email' => 'updated.email@example.com',
        ]);
    }

    /**
     * Test that the destroy method deletes the user and associated transactions.
     *
     * @return void
     */
    public function test_destroy_user()
    {
        // Create a user and associated transactions
        $user = User::factory()->create();
        $transaction1 = Transaction::factory()->create(['user_id' => $user->id]);
        $transaction2 = Transaction::factory()->create(['user_id' => $user->id]);

        // Ensure transactions exist before the request
        $this->assertEquals(2, $user->transactions()->count());

        // Send a DELETE request to remove the user
        $response = $this->json('DELETE', "/api/v1/users/{$user->id}");

        // Assert that the response has status 200
        $response->assertStatus(200);

        // Assert the correct deletion message
        $response->assertJson([
            'message' => 'User details retrieved successfully, and the associated transactions have been deleted.',
        ]);

        // Assert that the user is deleted
        $this->assertNull(User::find($user->id));

        // Assert that the transactions are also deleted
        $this->assertEquals(0, $user->transactions()->count());
    }

    /**
     * Test that the show method retrieves user details without deleting transactions.
     *
     * @return void
     */
    public function test_show_user_and_dont_delete_transactions()
    {
        // Create a user and associated transactions
        $user = User::factory()->create();
        $transaction1 = Transaction::factory()->create(['user_id' => $user->id]);
        $transaction2 = Transaction::factory()->create(['user_id' => $user->id]);

        // Ensure transactions exist before the request
        $this->assertEquals(2, $user->transactions()->count());

        // Send a GET request to the 'show' method for the user
        $response = $this->json('GET', "/api/v1/users/{$user->id}");

        // Assert that the response has status 200
        $response->assertStatus(200);

        // Assert that the response contains the correct message
        $response->assertJson([
            'message' => 'User details retrieved successfully.',
        ]);

        // Assert that transactions are still present in the database
        $this->assertEquals(2, $user->transactions()->count());

        // Ensure that the transactions are actually present in the database
        $this->assertDatabaseHas('transactions', ['user_id' => $user->id, 'id' => $transaction1->id]);
        $this->assertDatabaseHas('transactions', ['user_id' => $user->id, 'id' => $transaction2->id]);
    }

    /**
     * Test that the show method returns a 404 when user is not found.
     *
     * @return void
     */
    public function test_show_user_not_found()
    {
        // Send a GET request for a non-existing user
        $response = $this->json('GET', '/api/v1/users/9999'); // ID 9999 doesn't exist

        // Assert that the response has status 404
        $response->assertStatus(404);

        // Assert that the response contains the expected message
        $response->assertJson([
            'message' => 'User not found.'
        ]);
    }
}
