<?php

namespace App\Http\Requests\IU;

use App\DataObject\AF\EventTypeData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IuGetEventsRequest extends FormRequest
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
            'from' => 'required|date|',
            'to'   => 'required|date|after_or_equal:from|max_diff_in_months:from,2',
            'type' => [
                'nullable',
                'array'
            ],
            'type.*' => [
                Rule::in(array_values(EventTypeData::getConstants()))
            ]
        ];
    }
}
