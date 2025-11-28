<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Account;
use App\Models\Transactions;

class DepositController extends BaseController
{
    public function store (Request $request)
    {
        $this->validate($request, [
            'amount' => 'required|numeric|min:0.01',
            'account_id' => 'required|exists:accounts,id',
        ]);

        try {
            DB::beginTransaction();
            $account = Account::find($request->account_id);
            $account->balance += $request->amount;
            $account->save();
    
            $transaction = new Transactions;
            $transaction_arr = [
                'transaction_type' => 'deposit',
                'amount' => $request->amount,
                'account_id' => $request->account_id,
                'user_id' => auth()->id(),
                'descriiption' => $request->description ?? 'Deposit transaction',
            ];
            $transaction->create($transaction_arr);
            DB::commit();

            return $this->sendResponse([], 'Deposit successful.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), [], 500);
        }
    }
}
