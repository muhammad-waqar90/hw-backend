<?php

namespace App\Http\Controllers\GU;

use App\Http\Controllers\Controller;
use App\Repositories\GU\GuEbookRepository;
use App\Transformers\GU\Cart\GuCartCourseEbooksTransformer;

class GuEbookController extends Controller
{

    private GuEbookRepository $guEbookRepository;

    public function __construct(GuEbookRepository $guEbookRepository)
    {
        $this->guEbookRepository = $guEbookRepository;
    }

    public function getEbookListPerLevel($courseId)
    {
        $data = $this->guEbookRepository->getEbookListPerLevel($courseId);

        $fractal = fractal($data, new GuCartCourseEbooksTransformer());
        return response()->json($fractal, 200);
    }

}
