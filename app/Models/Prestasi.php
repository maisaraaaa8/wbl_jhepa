<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prestasi extends Model
{
    protected $table      = 'prestasi';
    protected $primaryKey = 'id';          // ← DB anda guna 'id'
    public    $timestamps = false;         // DB anda tiada created_at/updated_at

    protected $fillable = [
        'id_pelajar',
        'semester',
        'gpa',
        'cgpa',
    ];

    protected $casts = [
        'gpa'  => 'float',
        'cgpa' => 'float',
    ];

    public function pelajar()
    {
        return $this->belongsTo(Pelajar::class, 'id_pelajar', 'id_pelajar');
    }

    // Helper status dari CGPA
    public static function statusDariGpa(float $gpa): string
    {
        if ($gpa >= 3.50) return 'Cemerlang';
        if ($gpa >= 3.00) return 'Memuaskan';
        return 'Perlu Perhatian';
    }
}
