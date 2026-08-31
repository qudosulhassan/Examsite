<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certification extends Model
{
    protected $fillable = [
        'vendor_id',
        'name',
        'slug',
        'description',
        'is_active',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function exams()
    {
        return $this->belongsToMany(Exam::class);
    }
}
