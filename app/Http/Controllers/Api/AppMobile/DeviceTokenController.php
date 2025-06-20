<?php

namespace App\Http\Controllers\Api\AppMobile;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'token'    => 'required|string',
            'platform' => 'nullable|string',
        ]);

        $user = $request->user();

        $token = DeviceToken::updateOrCreate(
            ['token' => $data['token']],
            ['user_id' => $user->id, 'platform' => $data['platform'] ?? null]
        );

        return response()->json(['device_token' => $token], 201);
    }
}
