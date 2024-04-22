<?php

namespace App\Http\Requests\AF\Faqs;

use Illuminate\Foundation\Http\FormRequest;

class AfCreateUpdateFaq extends FormRequest
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
            'faq_category_id' => 'required|integer',
            'question'   => 'required|string|min:4|max:255',
            'short_answer'   => 'required|string|min:4|max:255',
            'answer'   => 'required|string|min:4|max:10000'
        ];
    }
}
