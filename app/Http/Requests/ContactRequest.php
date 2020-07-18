<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
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
            'subject' => 'required|max:30',
            'name' => 'required|max:30',
            'email' => 'required|string|email',
            'contact' => 'required|string'
        ];
    }

    public function messages()
    {
        return [
            'subject.max' => '件名は30文字以内で入力して下さい。',
            'subject.required' => '件名は入力必須です。',
            'name.required' => 'お名前は入力必須です。',
            'name.max' => 'お名前は30文字以内で入力して下さい。',
            'email.email' => '有効なメールアドレスを指定してください。',
            'contact.required' => 'お問い合わせ内容は入力必須です。',
            // 'contact.max' => 'お名前は30文字以内で入力して下さい。',
        ];
    }
}
