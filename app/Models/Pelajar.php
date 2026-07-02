<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelajar extends Model
{
    protected $table = 'pelajar'; // Nama table anda
    protected $primaryKey = 'id_pelajar';
    public $incrementing = true; // Nama PK anda
    public $timestamps = false; // Jika tiada created_at/updated_at di DB

    protected $fillable = [
        'user_id', 
        'nama_pelajar', 
        'no_matrik', 
        'semester', 
        'status_pengajian', 
        'tarikh_tamat_tajaan'
    ];
}