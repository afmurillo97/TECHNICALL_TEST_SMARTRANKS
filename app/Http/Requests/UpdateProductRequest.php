<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Number;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $method = $this->method();

        if ($method == 'PUT') {
            return [
                'category_id' => ['required', 'exists:categories,id'],
                'name' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:500'],
                'purchase_price' => ['required', 'numeric', 'min:0.01'],
                'sale_price' => ['required', 'numeric', 'min:0.05', 'gte:purchase_price'],
                'stock' => ['nullable', 'integer', 'min:0'],
                'featured_image' => ['nullable', 'string'],
                'status' => ['required', 'boolean'],
            ];
        }

        return [
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:500'],
            'purchase_price' => ['sometimes', 'required', 'numeric', 'min:0.01'],
            'sale_price' => [
                'sometimes',
                'required',
                'numeric',
                'min:' . (float)$this->route('product')->purchase_price
            ],
            'stock' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'featured_image' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'required', 'boolean'],
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
            $this->merge(['description' => $this->filled('description') 
            ? Str::of($this->input('description'))->trim()->ucfirst()->toString()
            : null]);
        }
        if ($this->has('purchase_price')) {
            $this->merge(['purchase_price' => round((float) $this->input('purchase_price'), 2)]);
        }
        if ($this->has('sale_price')) {
            $this->merge(['sale_price' => round((float) $this->input('sale_price'), 2)]);
        }
        if ($this->has('stock')) {
            $this->merge(['stock' => (int) $this->input('stock')]);
        }
        if ($this->has('status')) {
            $this->merge(['status' => (bool) $this->input('status')]);
        }
    }
}
