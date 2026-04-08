<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StaffAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Cek email di tabel USERS (bukan pelanggans)
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password staff salah.',
            ], 401);
        }

        // Buat token menggunakan Sanctum
        $token = $user->createToken('staff_token')->plainTextToken;

        return response()->json([
            'message' => 'Login Berhasil',
            'token' => $token,
            'user' => $user // Ini akan mengirim data jabatan juga
        ]);
    }
}