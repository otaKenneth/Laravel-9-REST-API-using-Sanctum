<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class AppsController extends BaseController
{
    public function store (Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'secret' => 'required|string|max:255',
            'url' => 'required|url|max:255',
        ]);

        $apiKey = Str::upper(Str::random(20));

        try {
            $app = new \App\Models\App;
            $result = $app->create([
                'name' => $request->name,
                'secret' => Crypt::encryptString($request->secret),
                'api_key' => $apiKey,
                'url' => $request->url,
            ]);

            return $this->sendResponse([
                'name' => $result->name,
                'api_key' => $result->api_key
            ], "App registered successfully");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 400);
        }
    }
}
