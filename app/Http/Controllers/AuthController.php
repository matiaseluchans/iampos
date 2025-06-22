<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function getUser(Request $request)
    {
        return new UserResource($request->user()->load([
            'roles',
            'tenant'
        ]));
    }

    public function validateToken(Request $request)
    {
        return response()->json([
            'valid' => true,
            'user' => $request->user()
        ]);
    }
}
