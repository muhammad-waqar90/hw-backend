<?php

namespace App\Http\Requests\AF\Users;

use App\DataObject\Purchases\PurchaseItemTypeData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AfUserPurchaseListRequest extends FormRequest
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
            'searchText' => [
                'string',
                'nullable',
                'max:100',
            ],
            'searchId' => [
                'integer',
                'nullable',
                'min:1',
            ],
            'type' => [
                'string',
                'nullable',
                Rule::in(array_values(PurchaseItemTypeData::getConstants())),
            ],
            'priceFrom' => [
                'numeric',
                'min:0',
                'max:999999',
            ],
            'priceTo' => [
                'numeric',
                'min:0',
                'max:999999',
            ],
            'dateFrom' => [
                'date',
            ],
            'dateTo' => [
                'date',
            ],
        ];
    }
}
