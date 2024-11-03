<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PurchaseRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'challan_no' => ['string', 'required'],
            'type' => ['string', 'required'],
            'challan_date' => ['required'],
            'stock_branch' => ['required', 'integer'],
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'   => false,
            'data' => [],
            'message'   => 'Validation errors',
            'errors'      => $validator->errors()
        ]));
    }
}
