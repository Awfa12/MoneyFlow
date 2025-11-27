<?php

use App\Models\User;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Str;


test('unauthenticated user cannot access transaction list', function () {
    // Make request without authentication token
    $response = $this->getJson('/api/transactions');
    
    // Should return 401 Unauthorized
    $response->assertStatus(401);
});

test('unauthenticated user cannot view single transaction', function () {
    // Make request without authentication token
    $response = $this->getJson('/api/transactions/some-uuid');
    
    // Should return 401 Unauthorized
    $response->assertStatus(401);
});

test('authenticated user can view their transaction list', function () {
    // Create two users with wallets
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    
    // Create a transaction between them
    $transaction = Transaction::create([
        'uuid' => Str::uuid(),
        'sender_wallet_id' => $user1->wallet->id,
        'recipient_wallet_id' => $user2->wallet->id,
        'amount' => 50.00,
        'status' => 'completed',
        'description' => 'Test payment',
    ]);
    
    // Authenticate as user1
    $token = $user1->createToken('test-token')->plainTextToken;
    
    // Make authenticated request
    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson('/api/transactions');
    
    // Should return 200 OK
    $response->assertStatus(200);
    
    // Should have pagination structure
    $response->assertJsonStructure([
        'data',
        'current_page',
        'last_page',
        'per_page',
        'total',
    ]);
    
    // Should include the transaction
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.uuid', (string) $transaction->uuid);
});

test('user cannot see transactions they are not involved in', function () {
    // Create three users
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();
    
    // Create a transaction between user2 and user3 (user1 is not involved)
    $transaction = Transaction::create([
        'uuid' => Str::uuid(),
        'sender_wallet_id' => $user2->wallet->id,
        'recipient_wallet_id' => $user3->wallet->id,
        'amount' => 100.00,
        'status' => 'completed',
        'description' => 'Private transaction',
    ]);
    
    // Authenticate as user1
    $token = $user1->createToken('test-token')->plainTextToken;
    
    // Make authenticated request
    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson('/api/transactions');
    
    // Should return 200 OK
    $response->assertStatus(200);
    
    // But should have zero transactions (user1 is not involved)
    $response->assertJsonCount(0, 'data');
    $response->assertJsonPath('total', 0);
});


test('authenticated user can view their own transaction by UUID', function () {
    // Create two users
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    
    // Create a transaction
    $transaction = Transaction::create([
        'uuid' => Str::uuid(),
        'sender_wallet_id' => $user1->wallet->id,
        'recipient_wallet_id' => $user2->wallet->id,
        'amount' => 75.00,
        'status' => 'completed',
        'description' => 'Test transaction',
    ]);
    
    // Authenticate as user1 (sender)
    $token = $user1->createToken('test-token')->plainTextToken;
    
    // Make authenticated request to view the transaction
    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson("/api/transactions/" . (string) $transaction->uuid);
    
    // Should return 200 OK
    $response->assertStatus(200);
    
    // Should have the correct structure
    $response->assertJsonStructure([
        'data' => [
            'uuid',
            'type',
            'amount',
            'sender',
            'recipient',
            'description',
            'status',
            'created_at',
            'updated_at',
        ],
    ]);
    
    // Should have the correct transaction data
    $response->assertJsonPath('data.uuid', (string) $transaction->uuid);
    $response->assertJsonPath('data.amount', '75.00');
    $response->assertJsonPath('data.type', 'sent'); // user1 is the sender
    $response->assertJsonPath('data.status', 'completed');
});

test('user cannot view transaction they are not involved in', function () {
    // Create three users
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();
    
    // Create a transaction between user2 and user3 (user1 is not involved)
    $transaction = Transaction::create([
        'uuid' => Str::uuid(),
        'sender_wallet_id' => $user2->wallet->id,
        'recipient_wallet_id' => $user3->wallet->id,
        'amount' => 200.00,
        'status' => 'completed',
        'description' => 'Private transaction',
    ]);
    
    // Authenticate as user1 (not involved in the transaction)
    $token = $user1->createToken('test-token')->plainTextToken;
    
    // Try to view the transaction
    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson("/api/transactions/" . (string) $transaction->uuid);
    
    // Should return 404 (not found/unauthorized)
    $response->assertStatus(404);
    
    // Should have error message
    $response->assertJson([
        'message' => 'Transaction not found or unauthorized access.',
    ]);
});