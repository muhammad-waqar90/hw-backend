<?php

namespace App\Http\Requests\AF\Tickets;

use App\DataObject\Tickets\TicketCategoryData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AfUpdateTicketCategoryRequest extends FormRequest
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
            'categoryId'        => [
                'required',
                'integer',
                Rule::in(array_values(TicketCategoryData::getConstants()))
            ]
        ];
    }
}
