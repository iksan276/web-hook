<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ViewPegawai;
use App\Models\SimpegPosisiJabatan;
use App\Models\SimpegJabatan;
use Illuminate\Support\Facades\DB;

class UserController extends BaseController
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
        if (!$decodedToken || $decodedToken != 'login-sso-itp') {
            return response()->json(['error' => 'Invalid token'], 401);
        }
        
        $columns = $request->input('columns', ['*']);
        if (!is_array($columns)) {
            $columns = explode(',', $columns);
        }

        $orderBy = $request->input('order_by', 'PegawaiID');
        $sort = $request->input('sort', 'asc');
        $limit = $request->input('limit', 10);
        $search = $request->input('search');
        $password = $request->input('password');

        $query = ViewPegawai::select($columns);

        // Jika ada pencarian, hanya cari di kolom emailG
        if ($search) {
            $query->where('emailG', 'LIKE', '%' . $search . '%');
        }

        // Jika ada pencarian password, gunakan raw query untuk mencari dengan PASSWORD()
        if ($password) {
            // Menggunakan DB::raw untuk menjalankan fungsi PASSWORD() MySQL
            $hashedPassword = DB::raw("LEFT(PASSWORD('" . $password . "'),10)");
            $query->whereRaw("Password = " . $hashedPassword);
        }

        $pegawaiData = $query
            ->orderBy($orderBy, $sort)
            ->limit($limit)
            ->get();

        // Tambahkan data Posisi dan Jabatan ke setiap pegawai
        $data = $pegawaiData->map(function ($pegawai) {
            // Ambil data Posisi berdasarkan PosisiID
            $posisi = SimpegPosisiJabatan::find($pegawai->PosisiID);
            
            // Ambil data Jabatan berdasarkan JabatanID
            $jabatan = SimpegJabatan::find($pegawai->JabatanID);
            
            // Tambahkan data Posisi dan Jabatan ke objek pegawai
            $pegawai->Posisi = $posisi ? [
                'ID' => $posisi->PosisiID,
                'Nama' => $posisi->Nama
            ] : null;
            
            $pegawai->Jabatan = $jabatan ? [
                'ID' => $jabatan->JabatanID,
                'Nama' => $jabatan->Nama,
                'Struktural' => $jabatan->Struktural,
                'Senat' => $jabatan->Senat
            ] : null;
            
            return $pegawai;
        });

        return response()->json($data);
    }
}
