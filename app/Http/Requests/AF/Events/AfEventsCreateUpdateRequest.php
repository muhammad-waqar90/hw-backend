<?php

namespace App\Http\Requests\AF\Events;

use App\DataObject\AF\EventTypeData;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AfEventsCreateUpdateRequest extends FormRequest
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
            'title' => 'required|string|min:5|max:50',
            'description' => 'required|string|min:10|max:65535',
            'type' => ['required', 'integer', Rule::in(EventTypeData::getEventTypes())],
            'url' => 'required|url|min:10|max:2048',
            'img' => 'nullable|mimes:jpg,jpeg,png,gif|max_mb:10',
            'start_date' => 'required|date_format:Y-m-d\TH:i|after_or_equal:' . Carbon::now()->format('Y-m-d H:i'),
            'end_date' => 'required|date_format:Y-m-d\TH:i|after_or_equal:start_date'
        ];
    }
}
