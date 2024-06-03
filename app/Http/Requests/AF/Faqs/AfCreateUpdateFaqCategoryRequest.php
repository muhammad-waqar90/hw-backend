<?php

namespace App\Http\Requests\AF\Faqs;

use Illuminate\Foundation\Http\FormRequest;

class AfCreateUpdateFaqCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'   => [
                'required',
                'string',
                'min:4',
                'max:100',
            ],
            'faq_category_id' => [
                'present',
                'nullable',
                'integer',
            ],
        ];
    }
}
