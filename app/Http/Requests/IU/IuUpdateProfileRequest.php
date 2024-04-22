<?php

namespace App\Http\Requests\IU;

use Illuminate\Foundation\Http\FormRequest;
use App\DataObject\UserProfileData;
use Illuminate\Validation\Rule;

class IuUpdateProfileRequest extends FormRequest
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
            'gender'   => 'required|string|in:M,F',
            'country'  => 'required|string|min:3|max:100',
            'city'  => 'required|string|min:3|max:100',
            'address' => 'required|string|min:3|max:100',
            'postalCode' => 'required|string|min:3|max:20',
            'phoneNumber' => 'required|string|min:3|max:50|regex:/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/',
            'occupation' => ['required', 'string', Rule::in(array_column(UserProfileData::OCCUPATION_LIST, 'value'))],
            'facebookUrl' => 'present|nullable|url|min:10|max:255|regex:/facebook.com/',
            'instagramUrl' => 'present|nullable|url|min:10|max:255|regex:/instagram.com/',
            'twitterUrl' => 'present|nullable|url|min:10|max:255|regex:/twitter.com/',
            'linkedinUrl' => 'present|nullable|url|min:10|max:255|regex:/linkedin.com/',
            'snapchatUrl' => 'present|nullable|url|min:10|max:255|regex:/snapchat.com/',
            'youtubeUrl' => 'present|nullable|url|min:10|max:255|regex:/youtube.com/',
            'pinterestUrl' => 'present|nullable|url|min:10|max:255|regex:/pinterest.com/'
        ];
    }
}