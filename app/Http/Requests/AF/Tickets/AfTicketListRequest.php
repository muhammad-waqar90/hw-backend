<?php

namespace App\Http\Requests\AF\Tickets;

use App\DataObject\Tickets\TicketCategoryData;
use App\DataObject\Tickets\TicketStatusData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AfTicketListRequest extends FormRequest
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
            'subject' => 'string|nullable|max:100',
            'category'        => [
                'integer',
                Rule::in(array_values(TicketCategoryData::getConstants()))
            ],
            'status'        => [
                'integer',
                Rule::in(array_values(TicketStatusData::getConstants()))
            ],
        ];
    }
}
