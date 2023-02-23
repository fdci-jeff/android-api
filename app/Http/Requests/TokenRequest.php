<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class TokenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|boolean',
            'name' => 'required|boolean',
            'email' => 'required|boolean'
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Error! Bad Input',
            'data'      => $validator->errors()
        ]));
    }
    
    public function messages()
    {
        return [
            'id.required' => 'ID is required',
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
        ];
    }
}
