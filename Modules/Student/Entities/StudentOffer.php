<?php

namespace Modules\Student\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\OfferTemplate\Entities\OfferTemplate;

class StudentOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'intake_course_ids',
        'condition_title',
        'condition_description',
        'credit_title',
        'credit_description',
        'issue_date',
        'expiry_date',
        'offer_template_id',
        'path',
        'status'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function offerTemplate()
    {
        return $this->belongsTo(OfferTemplate::class);
    }

    protected static function newFactory()
    {
        return \Modules\Student\Database\factories\StudentOfferFactory::new();
    }
}
