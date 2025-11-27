<?php

use App\Models\User;
use App\Models\Wallet;

test('a user automatically gets a wallet when created', function () {
    // Create a new user
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    // Assert that a wallet was created automatically
    expect($user->wallet)->not->toBeNull();
    expect($user->wallet->balance)->toEqual(0);
    expect($user->wallet->currency)->toBe('EUR');
});