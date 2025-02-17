<?php

namespace App\Services\Auth;

use App\Exceptions\CustomMessageException;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * @param array $data
     * @return array
     * @throws \Throwable
     */
    public function register(array $data): array
    {
        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $token = $user->createToken('authToken')->plainTextToken;

            DB::commit();

            return ['token' => $token];
        } catch (\Throwable $throw) {
            DB::rollBack();
            throw $throw;
        }
    }

    /**
     * @param array $credentials
     * @return array
     * @throws ValidationException
     * @throws \Throwable
     */
    public function login(array $credentials): array
    {
        try {
            DB::beginTransaction();

            if (!Auth::attempt($credentials)) {
                throw new CustomMessageException('The provided credentials are incorrect.', 422);
            }

            $user = Auth::user();

            // Logout others devices
            $user->tokens()->delete();

            $token = $user->createToken('authToken')->plainTextToken;

            DB::commit();

            return ['token' => $token];
        } catch (\Throwable $throw) {
            DB::rollBack();
            throw $throw;
        }
    }

    /**
     * @param Authenticatable $user
     * @return string[]
     * @throws \Throwable
     */
    public function logout(Authenticatable $user): array
    {
        try {
            DB::beginTransaction();

            $user->currentAccessToken()->delete();

            DB::commit();

            return ['message' => 'Successfully logged out'];
        } catch (\Throwable $throw) {
            DB::rollBack();
            throw $throw;
        }
    }
}
