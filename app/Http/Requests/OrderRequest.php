<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class OrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'customer_id' => ['required', 'integer'],
            'branch_id' => ['required', 'integer'],
            'customer_address' => ['required', 'max:255'],
            'shipping_address' => ['max:255'],
            'branch_id' => ['required', 'integer'],
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
