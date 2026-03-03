<?php

namespace App\Http\Requests\MasterApp\Notification;

use Illuminate\Foundation\Http\FormRequest;

class MarkNotificationReadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'string'],
        ];
    }
}
