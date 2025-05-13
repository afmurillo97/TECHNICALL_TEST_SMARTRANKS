<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class BulkStoreCategoryRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            '*.name' => ['required', 'string', 'max:255'],
            '*.description' => ['nullable', 'string', 'max:500'],
            '*.featured_image' => ['nullable', 'string'],
            '*.status' => ['required', 'boolean'],
        ];
    }

    /**
     * This method is used to modify the input data before it is validated.
     * 
     * @return void
     */
    protected function prepareForValidation()
    {
        if ($this->has('name')) {
            $this->merge(['name' => Str::headline($this->input('name'))]);
        }
        
        if ($this->has('description')) {
            $this->merge([
                'description' => trim($this->input('description')) !== '' 
                    ? Str::of($this->input('description'))->trim()->ucfirst()->toString()
                    : null
            ]);
        }
        
        if ($this->has('status')) {
            $this->merge(['status' => (bool) $this->input('status')]);
        }
    }
}
