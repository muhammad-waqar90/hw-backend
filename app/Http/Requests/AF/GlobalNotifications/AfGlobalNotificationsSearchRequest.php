<?php

namespace App\Http\Requests\AF\GlobalNotifications;

use Illuminate\Foundation\Http\FormRequest;

class AfGlobalNotificationsSearchRequest extends FormRequest
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
            'searchText'    => [
                'string',
                'nullable',
                'max:100',
            ],
            'archiveStatus' => [
                'boolean',
            ],
        ];
    }
}
