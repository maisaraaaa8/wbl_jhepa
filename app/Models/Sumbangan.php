<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sumbangan extends Model
{
    protected $table      = 'sumbangan';
    protected $primaryKey = 'id';
    public    $timestamps = false;

    protected $fillable = [
        'id_pelajar',
        'id_keluarga_angkat',
        'jumlah',
        'tarikh_terima',
        'keterangan',
        'status',
        'bulan',
    ];

    protected $casts = [
        'jumlah'        => 'float',
        'tarikh_terima' => 'date',
    ];

    public function pelajar()
    {
        return $this->belongsTo(Pelajar::class, 'id_pelajar', 'id_pelajar');
    }

    public function keluargaAngkat()
    {
        return $this->belongsTo(KeluargaAngkat::class, 'id_keluarga_angkat', 'id_keluarga_angkat');
    }
}
