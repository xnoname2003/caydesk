<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Priority extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'color',
    ];

    public function slaRule()
    {
        return $this->hasOne(SlaRule::class);
    }
    
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
