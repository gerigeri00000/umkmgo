<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Umkm extends Model
{
    use HasFactory;

    protected $fillable = [
        'provinsi',
        'kabupaten',
        'kecamatan',
        'desa',
        'sls',
        'nama',
        'alamat',
        'website',
        'telepon',
        'jenis_tempat',
        'jenis_usaha',
        'kategori',
        'koordinat',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeByKategori($query, $kategori)
    {
        if ($kategori) {
            return $query->where('kategori', $kategori);
        }

        return $query;
    }

    public function scopeByKecamatan($query, $kecamatan)
    {
        if ($kecamatan) {
            return $query->where('kecamatan', $kecamatan);
        }

        return $query;
    }

    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where('nama', 'like', "%{$search}%");
        }

        return $query;
    }
}
