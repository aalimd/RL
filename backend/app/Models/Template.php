<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Template extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'layout_settings' => 'array',
        'is_active' => 'boolean',
        'draft_data' => 'array',
        'last_draft_saved_at' => 'datetime',
    ];

    public function requests()
    {
        return $this->hasMany(Request::class);
    }
}
