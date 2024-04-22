<?php

namespace App\Http\Requests\AF\Faqs;

use App\DataObject\FaqCategoryTypeData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AfFaqCategoryListRequest extends FormRequest
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
            'searchText'    => 'string|nullable|max:100',
            'type'        => [
                'integer',
                Rule::in(array_values(FaqCategoryTypeData::getConstants()))
            ],
        ];
    }
}
