<?php

namespace App\Http\Requests\AF\Refunds;

use App\DataObject\PaymentGatewaysData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AfRefundRequest extends FormRequest
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
            '*.id' => [
                'required',
                'integer',
                'min:1',
            ],
            '*.entity_type' => ['required', 'string', Rule::in([PaymentGatewaysData::STRIPE])],
        ];
    }
}
