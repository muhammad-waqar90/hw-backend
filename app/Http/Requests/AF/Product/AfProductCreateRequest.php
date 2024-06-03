<?php

namespace App\Http\Requests\AF\Product;

use Illuminate\Foundation\Http\FormRequest;

class AfProductCreateRequest extends FormRequest
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
            'category_id'   => [
                'required',
                'numeric',
                'exists:categories,id',
            ],
            'name'          => [
                'required',
                'string',
                'min:3',
                'max:50',
            ],
            'description'   => [
                'required',
                'string',
                'min:10',
                'max:65535',
            ],
            'product_metas' => [
                'nullable',
                'array',
            ], // TODO: required validation for the meta keys according to product type
            'img'           => [
                'required',
                'mimes:jpg,jpeg,png',
                'max_mb:10',
            ],
            'price'         => [
                'required',
                'gte:0',
                'lte:5000',
            ],
        ];
    }
}
