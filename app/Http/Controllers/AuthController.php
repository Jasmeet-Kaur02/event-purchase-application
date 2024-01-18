<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use stdClass;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = customValidate($request->all(), [
            'name' => "required|string",
            "email" => "required|email|unique:users,email",
            "password" => "required|string|min:8",
        ]);

        $validatedData['password'] = Hash::make($validatedData['password']);

        $user = User::create($validatedData);

        $token = $user->createToken($user->id)->plainTextToken;

        $data = new stdClass;
        $data->user = $user;
        $data->token = $token;

        return $this->success($data, "User account has been created successfully", 200);
    }

    public function login(Request $request)
    {
        $validatedData = customValidate($request->all(), [
            'email' => "required|email|exists:users,email",
            "password" => "required|string|min:8"
        ]);

        $user = User::where("email", $validatedData['email'])->first();

        if (!Hash::check($validatedData['password'], $user->password)) {
            return $this->error("Password is incorrect", 400);
        }

        $token = $user->createToken($user->id)->plainTextToken;

        $data = new stdClass;
        $data->user = $user;
        $data->token = $token;

        return $this->success($data, "User has been logged in successfully", 200);
    }

    public function logout(Request $request)
    {
        $validatedData = customValidate($request->all(), [
            'userId' => "required|integer|exists:users,id"
        ]);

        $user = User::find($validatedData['userId']);
        $user->tokens()->delete();

        return $this->success(true, "User has been logged out successfully", 200);
    }
}
