<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdersController extends BaseController
{
    public function index()
    {
        return $this->sendResponse(Order::with('items')->get(), "Successfully retrieved all orders.");
    }

    public function store(Request $request)
    {
        
    }
}
