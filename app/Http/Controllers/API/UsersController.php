<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use App\Models\UserDetails;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Validator;
use Illuminate\Http\Request;

class UsersController extends BaseController
{
    public function index()
    {
        return $this->sendResponse(User::all(), "Users retrieved successfully.");
    }

    public function show(User $user)
    {
        return $this->sendResponse($user, "User retrieved successfully.");
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|string'
        ]);
        
        $input = $request->all();

        try {
            DB::beginTransaction();
            $user = User::create([
                'role' => $input['role'],
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
            ]);
    
            $user->account()->create([
                'user_id' => $user->id,
                'account_number' => Str::upper(Str::random(10)),
                'balance' => 0,
            ]);
            DB::commit();

            return $this->sendResponse([
                "name" => $user->name,
            ], "You've created {$user->name} as a {$user->role} successfully.");
        } catch (\Excecption $e) {
            DB::rollBack();

            return $this->sendError("Server Error.", $e->getMessage(), [], 500);
        }
    }

    public function update(Request $request, User $user)
    {
        $input = $request->all();

        return $this->sendResponse($user, "User successfully updated.");
    }
}
