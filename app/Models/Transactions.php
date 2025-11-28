<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    use HasFactory;

    protected $table = 'transactions';
    protected $fillable = [
        'user_id',
        'account_id',
        'amount',
        'transaction_type',
        'description'
    ];

    public $timestamps = true;

    public function details()
    {
        return $this->hasMany(TransactionDetails::class);
    }
}
