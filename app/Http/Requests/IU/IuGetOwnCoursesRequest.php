<?php

namespace App\Http\Requests\IU;

use App\DataObject\CoursesData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IuGetOwnCoursesRequest extends FormRequest
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
            'searchText'        => [
                'string',
                'nullable',
                'max:100',
            ],
            'categoryId'        => [
                'integer',
                'nullable',
                'exists:categories,id',
            ],
            'order'             => [
                Rule::in(array_keys(CoursesData::OWNED_COURSES_ORDER)),
            ],
            'orderDirection' => [
                Rule::in(array_keys(CoursesData::ORDER_DIRECTION)),
            ],
        ];
    }
}
