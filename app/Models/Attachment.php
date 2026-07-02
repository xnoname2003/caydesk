<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attachment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'attachable_type',
        'attachable_id',
        'uploaded_by',
        'original_name',
        'stored_name',
        'path',
        'mime_type',
        'size',
    ];

    // Menghubungkan kembali ke Ticket atau Comment
    public function attachable()
    {
        return $this->morphTo();
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
