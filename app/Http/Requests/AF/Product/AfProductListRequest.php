<?php

namespace App\Http\Requests\AF\Product;

use Illuminate\Foundation\Http\FormRequest;

class AfProductListRequest extends FormRequest
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
            'searchText'    => [
                'nullable',
                'string',
                'max:100',
            ],
        ];
    }
}
