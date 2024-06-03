<?php

namespace App\Http\Controllers\AF;

use App\Http\Controllers\Controller;
use App\Http\Requests\AF\Faqs\AfCreateUpdateFaq;
use App\Http\Requests\AF\Faqs\AfCreateUpdateFaqCategoryRequest;
use App\Http\Requests\AF\Faqs\AfFaqCategoryListRequest;
use App\Http\Requests\AF\Faqs\AfFaqEntryListRequest;
use App\Repositories\AF\AfFaqRepository;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

class AfFaqController extends Controller
{
    private AfFaqRepository $afFaqRepository;

    public function __construct(AfFaqRepository $afFaqRepository)
    {
        $this->afFaqRepository = $afFaqRepository;
    }

    public function getFaqCategoryList(AfFaqCategoryListRequest $request)
    {
        $data = $this->afFaqRepository->getFaqCategoryListQuery($request->searchText, $request->type);
        $data = $data->paginate(20)
            ->appends([
                'searchText' => $request->searchText,
                'type' => $request->type,
            ]);

        return response()->json($data, 200);
    }

    public function createFaqCategory(AfCreateUpdateFaqCategoryRequest $request)
    {
        try {
            if ($request->faq_category_id) {
                $rootFaqCategory = $this->afFaqRepository->getFaqCategory($request->faq_category_id);
                if (! $rootFaqCategory) {
                    return response()->json(['errors' => 'Root category not found'], 404);
                }
                if ($rootFaqCategory->faq_category_id) {
                    return response()->json(['errors' => 'Cannot add subcategory to non root category'], 400);
                }
            }

            $this->afFaqRepository->createFaqCategory($request->name, $request->faq_category_id);

            return response()->json(['message' => 'Successfully created faq category'], 200);
        } catch (\Exception $e) {
            Log::error('Exception: AfFaqController@createFaqCategory', [$e->getMessage()]);
            if ($e->getCode() == 23000) {
                return response()->json(['errors' => 'Faq Category with the same name already exists or invalid parent category'], 400);
            }

            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    public function updateFaqCategory(AfCreateUpdateFaqCategoryRequest $request, $id)
    {
        try {
            $faqCategory = $this->afFaqRepository->getFaqCategoryWithChildCount($id);
            if (! $faqCategory) {
                return response()->json(['errors' => Lang::get('general.notFound')], 404);
            }
            if ($request->faq_category_id) {
                $rootFaqCategory = $this->afFaqRepository->getFaqCategory($request->faq_category_id);
                if (! $rootFaqCategory) {
                    return response()->json(['errors' => 'Root category not found'], 400);
                }
                if ($rootFaqCategory->faq_category_id) {
                    return response()->json(['errors' => 'Cannot add subcategory to non root category'], 400);
                }
            }
            //check if subcategory can be changed to root category
            if (! $request->faq_category_id && $faqCategory->faq_category_id && $faqCategory->faqs_count != 0) {
                return response()->json(['errors' => 'Cannot update subcategory to root category while it has Faqs associated with it'], 400);
            }
            //check if root category can be changed to subcategory
            if ($request->faq_category_id && ! $faqCategory->faq_category_id && $faqCategory->faq_categories_count != 0) {
                return response()->json(['errors' => 'Cannot update root category to subcategory while it has faq categories associated with it'], 400);
            }

            $this->afFaqRepository->updateFaqCategory($id, $request->name, $request->faq_category_id);

            return response()->json(['message' => 'Successfully updated faq category'], 200);
        } catch (\Exception $e) {
            Log::error('Exception: AfFaqController@updateFaqCategory', [$e->getMessage()]);
            if ($e->getCode() == 23000) {
                return response()->json(['errors' => 'Faq category with the same name already exists or invalid parent category'], 400);
            }

            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    public function getFaqCategory($id)
    {
        $faqCategory = $this->afFaqRepository->getFaqCategoryWithChildren($id);
        if (! $faqCategory) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        return response()->json($faqCategory, 200);
    }

    public function deleteFaqCategory($id)
    {
        $faqCategory = $this->afFaqRepository->getFaqCategory($id);
        if (! $faqCategory) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        $this->afFaqRepository->deleteFaqCategory($id);

        return response()->json(['message' => 'Successfully deleted faq category'], 200);
    }

    public function publishFaqCategory($id)
    {
        $faqCategory = $this->afFaqRepository->getFaqCategoryWithChildCount($id);
        if (! $faqCategory) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        //check for subcategories
        if ($faqCategory->faq_category_id && $faqCategory->published_faqs_count == 0) {
            return response()->json(['errors' => 'Cannot publish faq subcategory with no published faqs'], 400);
        }

        //check for root categories
        if (! $faqCategory->faq_category_id && $faqCategory->published_faq_categories_count == 0) {
            return response()->json(['errors' => 'Cannot publish faq root category with no published subcategories'], 400);
        }

        $faqCategory->published = 1;
        $faqCategory->save();

        return response()->json(['message' => 'Successfully published faq category'], 200);
    }

    public function unpublishFaqCategory($id)
    {
        $faqCategory = $this->afFaqRepository->getFaqCategory($id);
        if (! $faqCategory) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        $this->afFaqRepository->unpublishFaqCategory($id);

        return response()->json(['message' => 'Successfully unpublished faq category'], 200);
    }

    public function createFaq(AfCreateUpdateFaq $request)
    {
        try {
            $faqCategory = $this->afFaqRepository->getFaqCategory($request->faq_category_id);
            if (! $faqCategory || ! $faqCategory->faq_category_id) {
                return response()->json(['errors' => 'Invalid faq category'], 400);
            }

            $this->afFaqRepository->createFaq($request->faq_category_id, $request->question, $request->short_answer, $request->answer);

            return response()->json(['message' => 'Successfully created faq'], 200);
        } catch (\Exception $e) {
            Log::error('Exception: AfFaqController@createFaq', [$e->getMessage()]);
            if ($e->getCode() == 23000) {
                return response()->json(['errors' => 'Invalid faq category'], 400);
            }

            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    public function updateFaq(AfCreateUpdateFaq $request, $id)
    {
        try {
            $faq = $this->afFaqRepository->getFaq($id);
            if (! $faq) {
                return response()->json(['errors' => Lang::get('general.notFound')], 404);
            }

            $faqCategory = $this->afFaqRepository->getFaqCategory($request->faq_category_id);
            if (! $faqCategory || ! $faqCategory->faq_category_id) {
                return response()->json(['errors' => 'Invalid faq category'], 404);
            }

            $this->afFaqRepository->updateFaq($id, $request->faq_category_id, $request->question, $request->short_answer, $request->answer);

            return response()->json(['message' => 'Successfully updated faq'], 200);
        } catch (\Exception $e) {
            Log::error('Exception: AfFaqController@updateFaq', [$e->getMessage()]);
            if ($e->getCode() == 23000) {
                return response()->json(['errors' => 'Invalid faq category'], 400);
            }

            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    public function getFaqList(AfFaqEntryListRequest $request)
    {
        $data = $this->afFaqRepository->getFaqListQuery($request->searchText);
        $data = $data->paginate(20)->appends([
            'searchText' => $request->searchText,
        ]);

        return response()->json($data, 200);
    }

    public function getFaq($id)
    {
        $faq = $this->afFaqRepository->getFaqWithCategory($id);
        if (! $faq) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        return response()->json($faq, 200);
    }

    public function publishFaq($id)
    {
        $faq = $this->afFaqRepository->getFaq($id);
        if (! $faq) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        $faq->published = 1;
        $faq->save();

        return response()->json(['message' => 'Successfully published faq'], 200);
    }

    public function unpublishFaq($id)
    {
        $faq = $this->afFaqRepository->getFaq($id);
        if (! $faq) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        $this->afFaqRepository->unpublishFaq($id);

        return response()->json(['message' => 'Successfully unpublished faq'], 200);
    }

    public function deleteFaq($id)
    {
        $faq = $this->afFaqRepository->getFaq($id);
        if (! $faq) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        $this->afFaqRepository->deleteFaq($id);

        return response()->json(['message' => 'Successfully deleted faq'], 200);
    }

    public function getRootFaqCategoryList()
    {
        $data = $this->afFaqRepository->getRootFaqCategoryList();

        return response()->json($data, 200);
    }

    public function getFaqSubCategoryList()
    {
        $data = $this->afFaqRepository->getFaqSubCategoryList();

        return response()->json($data, 200);
    }
}
