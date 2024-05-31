<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendInquiryRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255',
            'contact_number' => 'required|max:255',
            'message' => 'required|max:255',
        ];
    }
}
