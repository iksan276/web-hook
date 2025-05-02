<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimpegJabatan extends Model
{
    protected $table = 'simpeg_jabatan';
    public $timestamps = false;
    protected $primaryKey = 'JabatanID';

    protected $fillable = [
        'JabatanID', 'Nama', 'Struktual', 'Senat', 'UCreate', 'DCreate', 'UEdited', 'DEdited', 'NA'
    ];
}
