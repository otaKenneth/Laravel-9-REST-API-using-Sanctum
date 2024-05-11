<?php

namespace App\Http\Controllers\API;

use App\Models\Stores;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StoreController extends BaseController
{
    public function index()
    {
        return $this->sendResponse(Stores::all(), "Stores successfully retrieved.");
    }

    public function show(Stores $id)
    {
        return $this->sendResponse([
            'data' => $id
        ], "Store successfully retrieved.");
    }

    public function store(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'owner' => 'required|exists:users,id',
            'name' => 'required|string',
            'address' => 'required',
            'city' => 'required',
            'province' => 'required',
            'country' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validator Error.', $validator->errors());
        }

        try {
            $store = Stores::create($input);
            return $this->sendResponse($store, "Store successfully created.");
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->sendError('Server Error.', $th->getMessage());
        }
    }
}
