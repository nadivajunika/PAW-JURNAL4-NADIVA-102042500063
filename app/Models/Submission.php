<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'user_id',
        'jawaban',
        'submitted_at',
        'nilai',
        'catatan_dosen',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
        ];
    }

    // Accessor Status Kelulusan Nilai
    public function getStatusKelulusanAttribute(): string
    {
        if (is_null($this->nilai)) {
            return 'Belum Dinilai';
        }

        return $this->nilai >= 75 ? 'Lulus' : 'Tidak Lulus';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }
}