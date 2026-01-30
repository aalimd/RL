<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'layout_settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function requests()
    {
        return $this->hasMany(Request::class);
    }
}
