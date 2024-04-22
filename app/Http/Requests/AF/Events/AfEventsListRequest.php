<?php

namespace App\Http\Requests\AF\Events;

use App\DataObject\AF\EventTypeData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AfEventsListRequest extends FormRequest
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
            'searchText' => 'string|nullable|max:100',
            'type' => ['integer', Rule::in(EventTypeData::getEventTypes())],
        ];
    }
}
