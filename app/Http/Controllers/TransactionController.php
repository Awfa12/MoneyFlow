<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    /**
     * Get all transactions for the authenticated user
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Get authenticated user and their wallet
        $user = $request->user();
        $wallet = $user->wallet;
        
        // Query transactions where user's wallet is sender OR recipient
        $transactions = Transaction::where('sender_wallet_id', $wallet->id)
            ->orWhere('recipient_wallet_id', $wallet->id)
            ->with(['senderWallet.user', 'recipientWallet.user']) // Eager load to avoid N+1 queries
            ->orderBy('created_at', 'desc') // Newest first
            ->paginate(15); // 15 transactions per page
        
        // Format transactions for better API response
        $formattedTransactions = $transactions->getCollection()->map(function ($transaction) use ($wallet) {
            $isSent = $transaction->sender_wallet_id === $wallet->id;
            
            return [
                'uuid' => $transaction->uuid,
                'type' => $isSent ? 'sent' : 'received',
                'amount' => number_format($transaction->amount, 2, '.', ''),
                'other_party' => $isSent 
                    ? $transaction->recipientWallet->user->name 
                    : $transaction->senderWallet->user->name,
                'other_party_email' => $isSent
                    ? $transaction->recipientWallet->user->email
                    : $transaction->senderWallet->user->email,
                'description' => $transaction->description,
                'status' => $transaction->status,
                'created_at' => $transaction->created_at->toIso8601String(),
            ];
        });
        
        return response()->json([
            'data' => $formattedTransactions,
            'current_page' => $transactions->currentPage(),
            'last_page' => $transactions->lastPage(),
            'per_page' => $transactions->perPage(),
            'total' => $transactions->total(),
        ], 200);
    }

    /**
     * Get a specific transaction by UUID
     * 
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function show(Request $request, string $uuid): JsonResponse
    {
        // Get authenticated user and their wallet
        $user = $request->user();
        $wallet = $user->wallet;
        
        // Find transaction by UUID with eager loading
        $transaction = Transaction::where('uuid', $uuid)
            ->with(['senderWallet.user', 'recipientWallet.user'])
            ->firstOrFail();
        
        // Verify user is either sender or recipient
        if ($transaction->sender_wallet_id !== $wallet->id && 
            $transaction->recipient_wallet_id !== $wallet->id) {
            return response()->json([
                'message' => 'Transaction not found or unauthorized access.',
            ], 404);
        }
        
        // Determine if user is sender or recipient
        $isSent = $transaction->sender_wallet_id === $wallet->id;
        
        // Format transaction details
        $formattedTransaction = [
            'uuid' => $transaction->uuid,
            'type' => $isSent ? 'sent' : 'received',
            'amount' => number_format($transaction->amount, 2, '.', ''),
            'sender' => [
                'name' => $transaction->senderWallet->user->name,
                'email' => $transaction->senderWallet->user->email,
            ],
            'recipient' => [
                'name' => $transaction->recipientWallet->user->name,
                'email' => $transaction->recipientWallet->user->email,
            ],
            'description' => $transaction->description,
            'status' => $transaction->status,
            'created_at' => $transaction->created_at->toIso8601String(),
            'updated_at' => $transaction->updated_at->toIso8601String(),
        ];
        
        return response()->json([
            'data' => $formattedTransaction,
        ], 200);
    }
}
