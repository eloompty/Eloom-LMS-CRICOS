<?php

namespace Modules\Student\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentEmail extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'email_id',
        'email_template_id',
        'type_id',
        'type',
        'sender_id',
        'sender_type'
    ];
    
    protected static function newFactory()
    {
        return \Modules\Student\Database\factories\StudentEmailFactory::new();
    }
}
