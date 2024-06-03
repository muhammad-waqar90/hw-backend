<?php

namespace App\Repositories\IU;

use App\Models\Faq;
use App\Models\FaqCategory;

class IuFaqRepository
{
    private Faq $faq;

    private FaqCategory $faqCategory;

    public function __construct(Faq $faq, FaqCategory $faqCategory)
    {
        $this->faq = $faq;
        $this->faqCategory = $faqCategory;
    }

    public function getRootFaqCategoryList()
    {
        return $this->faqCategory->where('faq_category_id', null)
            ->where('published', true)
            ->get();
    }

    public function getSubFaqCategoryList($id)
    {
        return $this->faqCategory->where('faq_category_id', $id)
            ->with('faqs', function ($query) {
                $query->select('id', 'question', 'faq_category_id', 'short_answer');
            })
            ->where('published', true)
            ->simplePaginate(15);
    }

    public function getFaqForCategory($id)
    {
        return $this->faq->select('id', 'faq_category_id', 'question', 'short_answer', 'published')
            ->where('faq_category_id', $id)
            ->where('published', true)
            ->get();
    }

    public function searchFaqQuery($searchText = '')
    {
        return $this->faq
            ->select('faqs.id', 'faqs.faq_category_id', 'faqs.question', 'faqs.short_answer', 'fc.name as category_name')
            ->when($searchText, function ($query) use ($searchText) {
                $query->where('faqs.question', 'LIKE', "%$searchText%")
                    ->orWhere('fc.name', 'LIKE', "%$searchText%");
            })
            ->join('faq_categories as fc', function ($query) {
                $query->on('fc.id', 'faqs.faq_category_id')
                    ->where('fc.published', true);
            })
            ->where('faqs.published', true);
    }

    public function getFaq($id)
    {
        return $this->faq->where('id', $id)
            ->where('published', 1)
            ->first();
    }
}
