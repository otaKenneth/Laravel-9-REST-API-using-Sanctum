<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Transactions;
use App\Models\User;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class TransactionController extends BaseController
{
    public function index()
    {
        return $this->sendResponse(Transactions::with('details')->get(), "Successfully retrieved all transactions.");
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'node_customer' => 'required',
            'transaction.*.product' => 'required',
            'transaction.*.quantity' => 'required|numeric',
            'transaction.*.price' => 'required|numeric'
        ]);

        $input = $request->all();

        $transaction = DB::transaction(function () use ($input) {
            return tap(
                Transactions::create([
                    'user_id' => $input['node_customer']
                ]),
                function (Transactions $transaction) use ($input) {
                    // Get user's preceding upline referrer
                    $user = User::find($transaction->user_id);
                    $precedingTeams = $user->precedingTeamLead();
                    $precedingTeamLeads_cnt = $precedingTeams->count();

                    // create a function to save shareable amount to above nodes
                    $transactions = array_map(function ($x) use ($transaction, $precedingTeamLeads_cnt) {
                        // compute for percentage to be taken out from the sale
                        $amount = $x['price'] * $x['quantity'];
                        $shareable = $amount * ($precedingTeamLeads_cnt / 100);
                        $finalAmount = $amount - $shareable;

                        return [
                            'item_id' => $x['product'],
                            'quantity' => $x['quantity'],
                            'price' => $finalAmount,
                            'shareable' => $shareable,
                            'lead_cnt' => $precedingTeamLeads_cnt
                        ];
                    }, $input['transaction']);
                    // dd($transactions);
                    foreach ($transactions as $key => $value) {
                        extract($value);
                        unset($value['shareable']);
                        unset($value['lead_cnt']);
                        $transaction->details()->create($value);

                        $add_to_wallet = $shareable / $lead_cnt;
                        foreach ($precedingTeams->reverse() as $key => $lead_user) {
                            $leadu = User::find($lead_user->id);
                            $leadu->wallet += $add_to_wallet;
                            $leadu->save();
                        }
                    }
                }
            );
        });

        return $this->sendResponse($transaction, "Successfully created a transaction.");
    }

    public function withdraw(Request $request)
    {
        $this->validate($request, [
            'amount' => 'required|numeric|min:0.01',
            'account_id' => 'required|exists:accounts,id',
        ]);

        try {
            DB::beginTransaction();
            $account = Account::find($request->account_id);
            $account->balance -= $request->amount;
            $account->save();
    
            $transaction = new Transactions;
            $transaction_arr = [
                'transaction_type' => 'withdraw',
                'amount' => $request->amount,
                'account_id' => $request->account_id,
                'user_id' => auth()->id(),
                'description' => $request->description ?? 'Withdraw transaction',
            ];
            $transaction->create($transaction_arr);
            DB::commit();

            return $this->sendResponse([], 'Withdraw successful.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function transfer(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'amount' => 'required|numeric|min:0.01',
            'from_account_id' => 'required|exists:accounts,id',
            'to_account_id' => 'required|exists:accounts,id|different:from_account_id',
        ]);

        if ($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(), 400);
        }

        try {
            DB::beginTransaction();
            $from_account = Account::find($request->from_account_id);
            $to_account = Account::find($request->to_account_id);

            $from_account->balance -= $request->amount;
            $from_account->save();

            $to_account->balance += $request->amount;
            $to_account->save();
    
            $transaction = new Transactions;
            $transaction_arr = [
                'transaction_type' => 'transfer',
                'amount' => $request->amount,
                'account_id' => $request->from_account_id,
                'user_id' => auth()->id(),
                'description' => $request->description ?? 'Transfer transaction',
                'related_account_id' => $request->to_account_id,
            ];
            $transaction->create($transaction_arr);
            DB::commit();

            return $this->sendResponse([], 'Transfer successful.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function payment (Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'amount' => 'required|numeric|min:0.01',
            'from_account_number' => 'required|exists:accounts,account_number',
            'to_account_number' => 'required|exists:accounts,account_number|different:from_account_number',
        ]);

        if ($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(), 400);
        }

        try {
            DB::beginTransaction();
            $from_account = Account::where('account_number', $request->from_account_number)->first();
            $to_account = Account::where('account_number', $request->to_account_number)->first();

            $from_account->balance -= $request->amount;
            $from_account->save();

            $to_account->balance += $request->amount;
            $to_account->save();
    
            $transaction = new Transactions;
            $transaction_arr = [
                'transaction_type' => 'payment',
                'amount' => $request->amount,
                'account_id' => $from_account->id,
                'user_id' => $from_account->user_id,
                'description' => $request->description ?? 'Payment transaction',
                'related_account_id' => $to_account->id,
            ];
            $transaction->create($transaction_arr);
            DB::commit();

            return $this->sendResponse([], 'Payment successful.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), [], 500);
        }
    }
}
