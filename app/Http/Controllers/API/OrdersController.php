<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Order;
use App\Models\User;
use App\Models\UserPaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Validator;

class OrdersController extends BaseController
{
    public function index()
    {
        return $this->sendResponse(Order::with('items')->get(), "Successfully retrieved all orders.");
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'total_amount' => 'required|numeric',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer',
            'items.*.price' => 'required|numeric',
            'payment_method_id' => 'required|exists:user_payment_methods,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 400);
        }

        try {
            $user = $request->user();
            $order = DB::transaction(function () use ($request, $user) {
                $order = $user->orders()->create([
                    'total_amount' => $request->total_amount,
                    'status' => 'pending'
                ]);

                $order_items = $order->items()->createMany($request->items);

                return $order;
            });

            $order_transaction_number = 'ORD-' . strtoupper(uniqid());

            $payment = $order->payment()->create([
                'user_id' => $user->id,
                'payment_method_id' => $request->payment_method_id,
                'amount' => $request->total_amount,
                'status' => 'pending',
                'transaction_number' => $order_transaction_number
            ]);

            $payment_method = UserPaymentMethod::find($request->payment_method_id);
            $service_config = config('services.bank');

            $api_body = [
                'amount' => $request->total_amount,
                'from_account_number' => $payment_method->account_number,
                'to_account_number' => $service_config['my_account']
            ];
            
            $response = Http::withHeaders([
                'X-API-KEY' => $service_config['key'],
                'X-SECRET-KEY' => $service_config['secret'],
                'Content-Type' => 'application/json'
            ])->post($service_config['domain'] . '/api/payment', $api_body);

            if ($response->successful()) {
                $result = $response->json();
                $payment->status = 'paid';
                $payment->save();
            } else {
                $result = $response->json();
                $data = $result['data'] ?? [];
                DB::rollBack();
                return $this->sendError("Payment Error: " . $result['message'], $data, 400);
            }

            DB::commit();

            return $this->sendResponse([
                'order_number' => $order_transaction_number
            ], "Order successful!");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError("Order creation failed.", ['error' => $e->getMessage()], 500);
        }
    }

    public function updateStatus (Request $request, Order $order)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'status' => "required|string"
        ]);

        if ($validator->fails()) {
            return $this->sendError("Validation Error.", $validator->errors(), 400);
        }

        try {
            $order->status = $input['status'];
            $order->save();

            return $this->sendResponse(["order_id" => $order->id], "Status changed.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 400);
        }
    }

    public function update (Order $order)
    {
        try{
            $payment = $order->payment;
            $payment_method = UserPaymentMethod::where('id', $payment->payment_method_id)->first();
            $service_config = config('services.bank');

            $api_body = [
                'amount' => $order->total_amount,
                'from_account_number' => $payment_method->account_number,
                'to_account_number' => $service_config['my_account']
            ];
            
            $response = Http::withHeaders([
                'X-API-KEY' => $service_config['key'],
                'X-SECRET-KEY' => $service_config['secret'],
                'Content-Type' => 'application/json'
            ])->post($service_config['domain'] . '/api/payment', $api_body);

            if ($response->successful()) {
                $result = $response->json();
                $payment->status = 'paid';
                $payment->save();
            } else {
                $result = $response->json();
                $data = $result['data'] ?? [];
                DB::rollBack();
                return $this->sendError("Payment Error: " . $result['message'], $data, 400);
            }

            DB::commit();

            return $this->sendResponse([
                'order_number' => $payment->transaction_number
            ], "Order successful!");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 400);
        }
    }
}
