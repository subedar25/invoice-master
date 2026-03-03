<?php
namespace App\Http\Requests\MasterApp\Modules;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ModulesUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $moduleId = $this->route('module');
        return [

            'name' => 'required|string|max:255|unique:modules,name,' . $moduleId,
            'slug' => 'required|string|max:255|unique:modules,slug,' . $moduleId,
        ];
    }
   
}
