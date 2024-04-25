<?php

namespace App\Http\Requests\AF\Lessons\Faqs;

use Illuminate\Foundation\Http\FormRequest;

class AfLessonFaqListRequest extends FormRequest
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
            'searchText'    =>  'string|nullable|max:100'
        ];
    }
}