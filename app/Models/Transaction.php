<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Wallet;

class Transaction extends Model
{
    protected $fillable = [
        'uuid',
        'sender_wallet_id',
        'recipient_wallet_id',
        'amount',
        'status',
        'description',
    ];

    public function senderWallet()
    {
        return $this->belongsTo(Wallet::class, 'sender_wallet_id');
    }

    public function recipientWallet()
    {
        return $this->belongsTo(Wallet::class, 'recipient_wallet_id');
    }
}
