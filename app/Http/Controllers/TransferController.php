<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TransferService;
use App\Exceptions\InsufficientFundsException;
use Illuminate\Http\JsonResponse;

class TransferController extends Controller
{
    /**
     * Transfer money to another user
     * 
     * @param Request $request
     * @param TransferService $transferService
     * @return JsonResponse
     */
    public function store(Request $request, TransferService $transferService): JsonResponse
    {
        // Validate the request data
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        // Get authenticated user and their wallet
        $sender = $request->user(); // Gets authenticated user from Sanctum token
        $senderWallet = $sender->wallet; // Gets wallet via relationship

        // Get recipient user and their wallet
        $recipient = \App\Models\User::findOrFail($validated['recipient_id']);
        $recipientWallet = $recipient->wallet;

        // Prevent self-transfers
        if ($sender->id === $recipient->id) {
            return response()->json([
                'message' => 'You cannot transfer money to yourself.',
            ], 422);
        }

        // Call TransferService
        try {
            $transaction = $transferService->transfer(
                $senderWallet->id,
                $recipientWallet->id,
                $validated['amount'],
                $validated['description'] ?? null
            );

            // Refresh wallet to get updated balance
            $senderWallet->refresh();

            // Return success response
            return response()->json([
                'message' => 'Transfer completed successfully',
                'transaction' => [
                    'uuid' => $transaction->uuid,
                    'amount' => $transaction->amount,
                    'recipient' => $recipient->name,
                    'description' => $transaction->description,
                ],
                'new_balance' => $senderWallet->balance,
            ], 200);

        } catch (InsufficientFundsException $e) {
            // Handle insufficient funds error
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
