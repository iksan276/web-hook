<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ViewPegawai;
use App\Models\SimpegPosisiJabatan;
use App\Models\SimpegJabatan;
use Illuminate\Support\Facades\DB;
use PDO;

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
        
        // Extract request parameters
        $columns = $request->input('columns', ['*']);
        if (!is_array($columns)) {
            $columns = explode(',', $columns);
        }

        $orderBy = $request->input('order_by', 'PegawaiID');
        $sort = strtolower($request->input('sort', 'asc'));
        $limit = (int)$request->input('limit', 10);
        $search = $request->input('search');
        $password = $request->input('password');

        try {
            // Get database connection directly and disable prepared statements
            $pdo = DB::connection()->getPdo();
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
            
            // Build the query
            $columnsStr = ($columns === ['*']) ? '*' : implode(', ', array_map(function($col) {
                return "`$col`";
            }, $columns));
            
            $sql = "SELECT $columnsStr FROM view_pegawai";
            $whereConditions = [];
            $params = [];
            
            if ($search) {
                $whereConditions[] = "emailG LIKE ?";
                $params[] = "%$search%";
            }
            
            if ($password) {
                // Get password hash using a direct query
                $stmt = $pdo->prepare("SELECT LEFT(PASSWORD(?), 10) as hash_value");
                $stmt->execute([$password]);
                $passwordHash = $stmt->fetch(PDO::FETCH_OBJ)->hash_value;
                
                $whereConditions[] = "Password = ?";
                $params[] = $passwordHash;
            }
            
            if (!empty($whereConditions)) {
                $sql .= " WHERE " . implode(' AND ', $whereConditions);
            }
            
            $sql .= " ORDER BY `$orderBy` $sort LIMIT $limit";
            
            // Prepare and execute without using Laravel's query builder
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $pegawaiData = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            // Process the results
            $data = collect($pegawaiData)->map(function ($pegawai) {
                // Ambil data Posisi berdasarkan PosisiID
                $posisi = property_exists($pegawai, 'PosisiID') && $pegawai->PosisiID ? 
                          SimpegPosisiJabatan::find($pegawai->PosisiID) : null;
                
                // Ambil data Jabatan berdasarkan JabatanID
                $jabatan = property_exists($pegawai, 'JabatanID') && $pegawai->JabatanID ? 
                          SimpegJabatan::find($pegawai->JabatanID) : null;
                
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
            
        } catch (\Exception $e) {
            // Return detailed error information
            return response()->json([
                'error' => 'Database error',
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ], 500);
        }
    }
}