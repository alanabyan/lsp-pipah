<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckJabatan
{
    public function handle(Request $request, Closure $next, ...$jabatans): Response
{
    // Ambil user yang lagi login, entah itu dari sanctum (staff) atau pelanggan
    $user = auth('sanctum')->user() ?: auth('pelanggan')->user();

    if (!$user) {
        return response()->json(['message' => 'Silakan login terlebih dahulu.'], 401);
    }

    // LOGIKA KRUSIAL: 
    // Kalau ada kolom jabatan (staff), pakai nilainya.
    // Kalau nggak ada (pelanggan), kita kasih string 'pelanggan'.
    $roleAktif = $user->jabatan ?? 'pelanggan';

    if (!in_array($roleAktif, $jabatans)) {
        return response()->json([
            'message' => 'Akses ditolak. Role Anda (' . $roleAktif . ') tidak diizinkan.'
        ], 403);
    }

    return $next($request);
}
}