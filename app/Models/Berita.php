<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Berita extends Model
{
    use HasFactory;

    // Daftar atribut yang dapat diisi secara massal
    protected $fillable = [
        'judul',
        'deskripsi',
        'slug',
        'foto',
        'id_user',
        'id_kategori',
    ];

    // Daftar atribut yang harus di-cast ke tipe data tertentu
    protected $casts = [
        'id_user' => 'integer',
        'id_kategori' => 'integer',
        'foto' => 'string',
    ];

    // Menentukan relasi banyak-ke-banyak dengan model Tag
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'tag_berita', 'id_berita', 'id_tag');
    }

    // Menentukan relasi satu-ke-satu dengan model Kategori
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori');
    }

    // Menentukan relasi satu-ke-satu dengan model User
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    // Menghasilkan slug secara otomatis
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($berita) {
            $berita->slug = Str::slug($berita->judul);
        });
    }
}
