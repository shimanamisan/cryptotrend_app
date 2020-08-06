<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MypagePasswordRequest extends FormRequest
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
            'old_password' => 'required|string|min:8|max:100|regex:/^[a-zA-Z0-9]+$/',
            'password' => 'required|string|confirmed|min:8|max:100|regex:/^[a-zA-Z0-9]+$/'
        ];
    }

    public function messages()
    {
        return [
            'old_password.regex' => '半角英数のみご利用いただけます。',
            'password.regex' => '半角英数のみご利用いただけます。',
            'password.confirmed' => ':attributeと、:attribute再入力が一致していません。',
        ];
    }
}
