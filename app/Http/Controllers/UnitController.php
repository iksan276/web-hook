<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SimpegPosisiJabatan;

class UnitController extends BaseController
{
    public function index(Request $request)
    {
        // Check authorization header
        $authHeader = $request->header('Authorization');
        if (!$authHeader) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        // Validate the authorization token
        $token = str_replace('Bearer ', '', $authHeader);
        $decodedToken = $this->secret($token, 'decryption');
        if (!$decodedToken) {
            return response()->json(['error' => 'Invalid token'], 401);
        }
        
        $columns = $request->input('columns', ['*']);

        // Ubah ke array jika dikirim sebagai string (contoh: columns=Nama,NA)
        if (!is_array($columns)) {
            $columns = explode(',', $columns);
        }

        $orderBy = $request->input('order_by', 'PosisiID');
        $sort = $request->input('sort', 'asc');
        $limit = $request->input('limit', 10);
        $search = $request->input('search');

        // Inisialisasi query
        $query = SimpegPosisiJabatan::select($columns);

        // Jika ada search keyword, apply ke semua kolom yang dipilih
        if ($search) {
            $query->where(function ($q) use ($search, $columns) {
                foreach ($columns as $col) {
                    $q->orWhere($col, 'LIKE', '%' . $search . '%');
                }
            });
        }

        // Eksekusi query
        $data = $query
            ->orderBy($orderBy, $sort)
            ->limit($limit)
            ->get();

        return response()->json($data);
    }
}
