<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PelangganAuthController extends Controller
{
    // POST /api/pelanggan/register
    public function register(Request $request)
    {
        $data = $request->validate([
            'nama_pelanggan' => ['required','string','max:150'],
            'email' => ['required','email','max:100','unique:pelanggan,email'],
            'password' => ['required','string','min:6'],

            'no_telp' => ['nullable','string','max:15'],
            'alamat1' => ['nullable','string','max:255'],
            'kota1' => ['nullable','string','max:255'],
            'propinsi1' => ['nullable','string','max:255'],
            'kodepos1' => ['nullable','string','max:255'],
        ]);

        $pelanggan = Pelanggan::create([
            'nama_pelanggan' => $data['nama_pelanggan'],
            'email' => $data['email'],
            'kata_kunci' => Hash::make($data['password']),
            'no_telp' => $data['no_telp'] ?? null,
            'alamat1' => $data['alamat1'] ?? null,
            'kota1' => $data['kota1'] ?? null,
            'propinsi1' => $data['propinsi1'] ?? null,
            'kodepos1' => $data['kodepos1'] ?? null,
        ]);

        $token = $pelanggan->createToken('pelanggan-token')->plainTextToken;

        return response()->json([
            'message' => 'Register berhasil',
            'token' => $token,
            'pelanggan' => $pelanggan,
        ], 201);
    }

    // POST /api/pelanggan/login
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required','email'],
            'password' => ['required','string'],
        ]);

        $pelanggan = Pelanggan::where('email', $data['email'])->first();

        if (! $pelanggan || ! Hash::check($data['password'], $pelanggan->kata_kunci)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }
        
        $pelanggan->tokens()->delete();
        $token = $pelanggan->createToken('pelanggan-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'pelanggan' => $pelanggan,
        ]);
    }

    // GET /api/pelanggan/me
    public function me(Request $request)
    {
        return response()->json([
            'pelanggan' => $request->user('pelanggan'),
        ]);
    }

    // POST /api/pelanggan/logout
    public function logout(Request $request)
    {
        $user = $request->user('pelanggan');
        $user->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil']);
    }
}