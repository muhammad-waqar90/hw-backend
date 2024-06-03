<?php

namespace App\Http\Controllers\IU;

use App\Http\Controllers\Controller;
use App\Repositories\IU\IuFaqRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class IuFaqController extends Controller
{
    private IuFaqRepository $iuFaqRepository;

    public function __construct(IuFaqRepository $iuFaqRepository)
    {
        $this->iuFaqRepository = $iuFaqRepository;
    }

    public function getRootFaqCategoryList()
    {
        $data = $this->iuFaqRepository->getRootFaqCategoryList();

        return response()->json($data, 200);
    }

    public function getSubFaqCategoryList($id)
    {
        $data = $this->iuFaqRepository->getSubFaqCategoryList($id);

        return response()->json($data, 200);
    }

    public function getFaqForCategory($id)
    {
        $data = $this->iuFaqRepository->getFaqForCategory($id);

        return response()->json($data, 200);
    }

    public function searchFaq(Request $request)
    {
        $data = $this->iuFaqRepository->searchFaqQuery($request->searchText);
        $data = $data->simplePaginate(15)
            ->appends([
                'searchText' => $request->searchText,
            ]);

        return response()->json($data, 200);
    }

    public function getFaq($id)
    {
        $faq = $this->iuFaqRepository->getFaq($id);
        if (! $faq) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        return response()->json($faq, 200);
    }
}
