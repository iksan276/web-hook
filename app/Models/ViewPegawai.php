<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewPegawai extends Model
{
    protected $table = 'view_pegawai';
    public $timestamps = false;
    protected $primaryKey = 'PegawaiID';

    protected $fillable = [
        'PegawaiID', 'NIK', 'NIDN', 'Homebase', 'PegawaiPilihanID',
        'Nama', 'EmailG', 'PosisiID', 'JabatanID', 'NA'
    ];
}
