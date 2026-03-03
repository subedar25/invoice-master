<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $phone = (string) ($this->input('phone') ?? '');
        $digits = preg_replace('/\D+/', '', $phone);

        if ($digits === '') {
            $this->merge(['phone' => null]);
            return;
        }

        if (strlen($digits) === 10) {
            $this->merge([
                'phone' => substr($digits, 0, 3) . '-' . substr($digits, 3, 3) . '-' . substr($digits, 6, 4),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'regex:/^\d{3}-\d{3}-\d{4}$/'],
        ];
    }
}
