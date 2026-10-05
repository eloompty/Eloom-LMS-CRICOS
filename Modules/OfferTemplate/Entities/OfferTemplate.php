<?php

namespace Modules\OfferTemplate\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OfferTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'path',
        'status'
    ];
    
    protected static function newFactory()
    {
        return \Modules\OfferTemplate\Database\factories\OfferTemplateFactory::new();
    }
}
