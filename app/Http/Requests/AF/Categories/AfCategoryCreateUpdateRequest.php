<?php

namespace App\Http\Requests\AF\Categories;

use Illuminate\Foundation\Http\FormRequest;

class AfCategoryCreateUpdateRequest extends FormRequest
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
            'root_category_id'   => 'nullable|integer',
            'parent_category_id' => 'nullable|integer|required_with:root_category_id',
            'name'               => 'required|string|min:3|max:50'
        ];
    }

    public function attributes()
    {
        return [
            'parent_category_id'   => 'Parent category',
            'root_category_id'     => 'Root category',
        ];
    }
}
