<?php

namespace App\Http\Requests\AF\Categories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AfCategoryCreateUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $categoryId = request()->route('id');

        return [
            'root_category_id'   => [
                'nullable',
                'integer',
            ],
            'parent_category_id' => [
                'nullable',
                'integer',
                'required_with:root_category_id',
            ],
            'name'               => [
                'required',
                'string',
                'min:3',
                'max:50',
                Rule::unique('categories')->ignore($categoryId),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'parent_category_id' => 'Parent category',
            'root_category_id' => 'Root category',
        ];
    }
}
