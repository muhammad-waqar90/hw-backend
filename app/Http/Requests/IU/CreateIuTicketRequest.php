<?php

namespace App\Http\Requests\IU;

use Illuminate\Foundation\Http\FormRequest;

class CreateIuTicketRequest extends FormRequest
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
            'subjectId' => [
                'required',
                'integer',
            ],
            'message'   => [
                'required',
                'string',
                'min:5',
                'max:4000',
            ],
            'assets'    => [
                'sometimes',
                'array',
                'max:3',
            ],
            'assets.*'  => [
                'required_with:assets.*',
                'image',
                'mimes:jpg,jpeg,png',
                'max:500',
            ],
            'log'       => [
                'present',
                'array',
                'max:65535',
            ],
        ];
    }
}
