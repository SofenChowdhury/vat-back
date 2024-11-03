<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

use Illuminate\Contracts\Validation\Validator;
class BranchRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:100',
            'contact_person' => 'required|string|max:100',
            'contact_mobile' => 'required|max:20|min:10',
            'contact_email' => 'required|email|max:100',
            'contact_address' => 'required|string|max:255',
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

    // public function messages()
    // {
    //     return ['name.required' => "Name should be mandatory"];
    // }

    
}
