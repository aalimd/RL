<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Request extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tracking_id',
        'student_name',
        'middle_name',
        'last_name',
        'student_email',
        'phone',
        'university',
        'gpa',
        'purpose',
        'deadline',
        'training_period',
        'template_id',
        'content_option',
        'custom_content',
        'status',
        'verify_token', // QR Verification Token (generated on approval)
        'verification_token', // Student Tracking ID Number (used for public tracking)
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
