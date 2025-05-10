<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Number;

class ProductFormRequest extends FormRequest
{
    /**
     * This method is used to modify the input data before it is validated.
     * 
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'name' => Str::headline($this->input('name')),
            'description' => $this->filled('description') 
            ? Str::of($this->input('description'))->trim()->ucfirst()->toString()
            : null,
            'purchase_price' => Number::format((float) $this->input('purchase_price'), 2),
            'sale_price' => Number::format((float)$this->input('sale_price'), 2),
            'stock' => (int) $this->input('stock'),
            'status' => (bool) $this->input('status'),
        ]);
    }

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
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'purchase_price' => 'required|numeric|min:0.01',
            'sale_price' => 'required|numeric|min:0.05|gte:purchase_price',
            'stock' => 'nullable|integer|min:0',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', //2MB MAX
            'status' => 'sometimes|boolean',
        ];
    }
}
