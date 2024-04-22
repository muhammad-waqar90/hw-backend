<?php

namespace App\Traits\Tests;

use App\Models\Faq;
use App\Models\FaqCategory;


trait FaqCategoryTestTrait {
    public function FaqFactorisation($factoryNumber) {
        $faqCategory = FaqCategory::factory($factoryNumber)->create();
        $faq = Faq::factory($factoryNumber)->create();

        $data = new \stdClass();

        $data->faqCategory = $faqCategory;
        $data->faq = $faq;

        return $data;
    }

    public function FaqFactorisationPublished($factoryNumber) {
        $faqCategory = FaqCategory::factory($factoryNumber)->published()->create();
        $faq = Faq::factory($factoryNumber)->published()->create();

        $data = new \stdClass();

        $data->faqCategory = $faqCategory;
        $data->faq = $faq;

        return $data;
    }


}
