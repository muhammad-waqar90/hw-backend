<?php

namespace App\Http\Requests\GU\Product;

use Illuminate\Foundation\Http\FormRequest;

class GuFetchProductsRequest extends FormRequest
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
            'name' => 'nullable|string',
            'category' => 'nullable|string',
            'price' => 'nullable|string',
            'is_available' => 'nullable',
            'is_not_bounded' => 'nullable',
        ];
    }
}
