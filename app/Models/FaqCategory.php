<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaqCategory extends Model
{
    use HasFactory;

    protected $guarded = [
        'id', 'created_at', 'updated_at',
    ];

    public function faqs()
    {
        return $this->hasMany(Faq::class);
    }

    public function publishedFaqs()
    {
        return $this->hasMany(Faq::class)->where('published', 1);
    }

    public function faqCategories()
    {
        return $this->hasMany(FaqCategory::class);
    }

    public function publishedFaqCategories()
    {
        return $this->hasMany(FaqCategory::class)->where('published', 1);
    }
}
