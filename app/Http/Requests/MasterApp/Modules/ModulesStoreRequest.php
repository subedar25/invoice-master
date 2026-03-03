<?php

namespace App\Http\Requests\MasterApp\Modules;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ModulesStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // only allow updates if the user is logged in
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
         return [
            'name' => 'required|string|max:255|unique:modules,name',
            'slug' => 'required|string|max:255|unique:modules,slug',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Module name already exists.',
            'slug.unique' => 'Module slug already exists.',
        ];
    }
}
