<?php

namespace App\Http\Requests\IU;

use Illuminate\Foundation\Http\FormRequest;

class IuUpdateUserAddressRequest extends FormRequest
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
            'country'  => 'required|string|min:3|max:100',
            'city'  => 'required|string|min:3|max:100',
            'address' => 'required|string|min:3|max:100',
            'postalCode' => 'required|string|min:3|max:20',
        ];
    }
}
