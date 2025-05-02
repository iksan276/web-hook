<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimpegPosisiJabatan extends Model
{
    protected $table = 'simpeg_posisi_jabatan';
    public $timestamps = false;
    protected $primaryKey = 'PosisiID';

    protected $fillable = [
        'PosisiID', 'Nama', 'UCreate', 'DCreate', 'UEdited', 'DEdited', 'NA'
    ];
}
