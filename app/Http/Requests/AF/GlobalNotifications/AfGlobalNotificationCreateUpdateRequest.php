<?php

namespace App\Http\Requests\AF\GlobalNotifications;

use Illuminate\Support\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AfGlobalNotificationCreateUpdateRequest extends FormRequest
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
            'title' => [
                'required',
                'string',
                'min:5',
                'max:50',
            ],
            'short_description' => [
                'required',
                'string',
                'min:5',
                'max:100',
            ],
            'description' => [
                'required',
                'string',
                'min:10',
                'max:65535',
            ],
            'archive_at' => [
                'required',
                'date',
                'after_or_equal:' . Carbon::now()->format('Y-m-d'),
            ],
            'show_modal' => ['integer', Rule::in([0,1])]
        ];
    }

    public function attributes()
    {
        return [
            'archive_at' => 'Archive date',
            'short_description' => 'Short description',
            'description' => 'Content',
        ];
    }
}
