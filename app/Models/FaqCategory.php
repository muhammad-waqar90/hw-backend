<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaqCategory extends Model
{
    use HasFactory;

    protected $guarded = [
        'id', 'created_at', 'updated_at'
    ];

    public function faqs()
    {
        return $this->hasMany('App\Models\Faq');
    }

    public function publishedFaqs()
    {
        return $this->hasMany('App\Models\Faq')->where('published', 1);
    }

    public function faqCategories()
    {
        return $this->hasMany('App\Models\FaqCategory');
    }

    public function publishedFaqCategories()
    {
        return $this->hasMany('App\Models\FaqCategory')->where('published', 1);
    }

}
