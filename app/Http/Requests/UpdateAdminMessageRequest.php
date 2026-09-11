<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return ['status' => ['required', 'string', Rule::in(['read', 'unread'])]];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'status.required' => 'Choose whether the message is read or unread.',
            'status.string' => 'Message status must be read or unread.',
            'status.in' => 'Message status must be read or unread.',
        ];
    }
}
