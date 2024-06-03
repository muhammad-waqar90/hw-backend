<?php

namespace App\Repositories\AF;

use App\DataObject\FaqCategoryTypeData;
use App\Models\Faq;
use App\Models\FaqCategory;

class AfFaqRepository
{
    private Faq $faq;

    private FaqCategory $faqCategory;

    public function __construct(Faq $faq, FaqCategory $faqCategory)
    {
        $this->faq = $faq;
        $this->faqCategory = $faqCategory;
    }

    public function getFaqCategoryListQuery($searchText = '', $type = null)
    {
        return $this->faqCategory
            ->when($searchText, function ($query) use ($searchText) {
                $query->where('name', 'LIKE', "%$searchText%");
            })
            ->when($type, function ($query) use ($type) {
                if ($type == FaqCategoryTypeData::ROOT_CATEGORY) {
                    $query->where('faq_category_id', null);
                } elseif ($type == FaqCategoryTypeData::SUB_CATEGORY) {
                    $query->where('faq_category_id', '!=', null);
                }
            })
            ->withCount('faqs')
            ->withCount('faqCategories')
            ->withCount('publishedFaqCategories')
            ->withCount('publishedFaqs')
            ->latest('id');
    }

    public function getRootFaqCategoryList()
    {
        return $this->faqCategory->where('faq_category_id', null)
            ->get();
    }

    public function getFaqSubCategoryList()
    {
        return $this->faqCategory->where('faq_category_id', '!=', null)
            ->oldest('name')
            ->get();
    }

    public function createFaqCategory($name, $faqCategoryId)
    {
        return $this->faqCategory->create([
            'name' => $name,
            'faq_category_id' => $faqCategoryId,
        ]);
    }

    public function updateFaqCategory($id, $name, $faqCategoryId)
    {
        $faqCategory = $this->faqCategory->where('id', $id)->first();
        $rootFaqCategoryId = $faqCategory->faq_category_id;

        $faqCategory->name = $name;
        $faqCategory->faq_category_id = $faqCategoryId;
        $faqCategory->save();

        if ($rootFaqCategoryId) {
            return $this->checkUnpublishRootFaqCategory($rootFaqCategoryId);
        }

        return true;
    }

    public function getFaqCategory($id)
    {
        return $this->faqCategory->where('id', $id)->first();
    }

    public function getFaqCategoryWithChildren($id)
    {
        return $this->faqCategory->where('id', $id)
            ->with('faqCategories')
            ->with('faqs')
            ->first();
    }

    public function unpublishFaqCategory($id)
    {
        $faqCategory = $this->faqCategory->where('id', $id)
            ->first();
        $faqCategory->published = 0;
        $faqCategory->save();

        if ($faqCategory->faq_category_id) {
            $this->checkUnpublishRootFaqCategory($faqCategory->faq_category_id);
        }

        return true;
    }

    public function deleteFaqCategory($id)
    {
        $faqCategory = $this->faqCategory->where('id', $id)
            ->first();

        $originalFaqCategoryId = $faqCategory->faq_category_id;
        $faqCategory->delete();

        if ($originalFaqCategoryId) {
            $this->checkUnpublishRootFaqCategory($originalFaqCategoryId);
        }

        return true;
    }

    public function checkUnpublishRootFaqCategory($id)
    {
        $faqCategory = $this->faqCategory->where('id', $id)
            ->withCount('publishedFaqCategories')
            ->first();
        if ($faqCategory->published_faq_categories_count) {
            return true;
        }

        $faqCategory->published = 0;
        $faqCategory->save();

        return true;
    }

    public function getFaqCategoryWithChildCount($id)
    {
        return $this->faqCategory
            ->where('id', $id)
            ->withCount('faqs')
            ->withCount('faqCategories')
            ->withCount('publishedFaqCategories')
            ->withCount('publishedFaqs')
            ->first();
    }

    public function createFaq($faqCategoryId, $question, $shortAnswer, $answer)
    {
        return $this->faq->create([
            'faq_category_id' => $faqCategoryId,
            'question' => $question,
            'short_answer' => $shortAnswer,
            'answer' => $answer,
        ]);
    }

    public function updateFaq($id, $faqCategoryId, $question, $shortAnswer, $answer)
    {
        $faq = $this->faq->where('id', $id)->first();

        $originalFaqCategoryId = $faq->faq_category_id;

        $faq->faq_category_id = $faqCategoryId;
        $faq->question = $question;
        $faq->short_answer = $shortAnswer;
        $faq->answer = $answer;
        $faq->save();

        $this->checkUnpublishFaqSubCategory($originalFaqCategoryId);
    }

    public function deleteFaq($id)
    {
        $faq = $this->faq->where('id', $id)
            ->first();

        $faqCategoryId = $faq->faq_category_id;
        $faq->delete();

        $this->checkUnpublishFaqSubCategory($faqCategoryId);

        return true;
    }

    public function getFaq($id)
    {
        return $this->faq->where('id', $id)->first();
    }

    public function getFaqWithCategory($id)
    {
        return $this->faq->where('id', $id)
            ->with('faqCategory')
            ->first();
    }

    public function getFaqListQuery($searchText = '')
    {
        return $this->faq->select('id', 'question', 'faq_category_id', 'question', 'short_answer', 'published', 'updated_at')
        ->when($searchText, function ($query) use ($searchText) {
            $query->where('question', 'LIKE', "%$searchText%");
        })
        ->with('faqCategory')
        ->latest('id');
    }

    public function unpublishFaq($id)
    {
        $faq = $this->faq->where('id', $id)
            ->first();
        $faq->published = 0;
        $faq->save();

        $this->checkUnpublishFaqSubCategory($faq->faq_category_id);

        return true;
    }

    public function checkUnpublishFaqSubCategory($id)
    {
        $faqCategory = $this->faqCategory->where('id', $id)
            ->withCount('publishedFaqs')
            ->first();
        if ($faqCategory->published_faqs_count) {
            return true;
        }

        $this->unpublishFaqCategory($id);

        return true;
    }
}
