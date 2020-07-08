<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MypageRequest extends FormRequest
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
            'name' => 'required|max:255',
            'email' => 'required|string|email',
            'password' => 'required|string|min:8|max:255|confirmed|regex:/^[a-zA-Z0-9]+$/'
        ];
    }

    public function messages()
    {
        return [
            'email.email' => '有効なメールアドレスを指定してください。',
            'regex' => '半角英数のみご利用いただけます。',
            'password.confirmed' => ':attributeと、:attribute再入力が一致していません。',
        ];
    }
}
