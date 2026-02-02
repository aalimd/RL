<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    protected $fillable = [
        'tracking_id',
        'student_name',
        'middle_name',
        'last_name',
        'student_email',
        'phone',
        'verification_token', // This might be an old column name from previous devs? The new one is verify_token
        'verify_token',
        'university',
        'purpose',
        'deadline',
        'training_period',
        'custom_content',
        'template_id',
        'status',
        'status',
        'telegram_chat_id',
        'admin_message',
        'rejection_reason',
        'document_path',
        'form_data',
    ];

    protected $casts = [
        'gpa' => 'decimal:2',
        'deadline' => 'date',
        'form_data' => 'array',
    ];

    public function template()
    {
        return $this->belongsTo(Template::class);
    }
}
