<?php

namespace App\Http\Requests\AF\TicketSubjects;

use App\DataObject\Tickets\TicketCategoryData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AfCreateUpdateTicketSubjectRequest extends FormRequest
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
            'categoryId' => [
                'required',
                'integer',
                Rule::in(array_values(TicketCategoryData::getConstants())),
            ],
            'name'              => [
                'string',
                'required',
                'min:4',
                'max:100',
                'unique:ticket_subjects,name,' . (request()->id ?: ''),
            ],
            'desc'              => [
                'string',
                'nullable',
                'max:10000',
            ],
            'only_logged_in'    => [
                'required',
                'boolean',
            ],
        ];
    }
}
