<?php

namespace App\Http\Requests\AF\Tickets;

use Illuminate\Foundation\Http\FormRequest;

class AfReplyToTicketRequest extends FormRequest
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
            'message'   => 'required|string|min:1|max:4000',
            'assets'    => 'sometimes|array|max:3',
            'assets.*'  => 'required_with:assets.*|image|mimes:jpg,jpeg,png|max:500',
        ];
    }
}
