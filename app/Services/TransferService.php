<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\Transaction;
use App\Exceptions\InsufficientFundsException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransferService
{
    /**
     * Transfer money from one wallet to another
     * 
     * @param int $senderWalletId
     * @param int $recipientWalletId
     * @param float $amount
     * @param string|null $description
     * @return Transaction
     * @throws InsufficientFundsException
     */
    public function transfer(int $senderWalletId, int $recipientWalletId, float $amount, ?string $description = null): Transaction
    {
        return DB::transaction(function () use ($senderWalletId, $recipientWalletId, $amount, $description)  {
            // Lock sender wallet (prevents concurrent access)
            $senderWallet = Wallet::where('id', $senderWalletId)
            ->lockForUpdate()  // ← This locks the row!
            ->firstOrFail();

            // Lock recipient wallet
            $recipientWallet = Wallet::where('id', $recipientWalletId)
            ->lockForUpdate()
            ->firstOrFail();

            // Check if sender has sufficient balance
            if ($senderWallet->balance < $amount) {
                throw new InsufficientFundsException(
                    "Insufficient funds. Your balance is €{$senderWallet->balance}, but you tried to send €{$amount}."
                );
            }


            // Deduct amount from sender's wallet
            $senderWallet->decrement('balance', $amount);

            // Add amount to recipient's wallet
            $recipientWallet->increment('balance', $amount);

            // Create transaction record
            $transaction = Transaction::create([
                'uuid' => Str::uuid(),
                'sender_wallet_id' => $senderWallet->id,
                'recipient_wallet_id' => $recipientWallet->id,
                'amount' => $amount,
                'status' => 'completed',
                'description' => $description,
            ]);

            // Return the transaction
            return $transaction;
        });
    }
}