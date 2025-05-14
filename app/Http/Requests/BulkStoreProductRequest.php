<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class BulkStoreProductRequest extends FormRequest
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
        return [
            '*.category_id' => ['required', 'exists:categories,id'],
            '*.name' => ['required', 'string', 'max:255'],
            '*.description' => ['nullable', 'string', 'max:500'],
            '*.purchase_price' => ['required', 'numeric', 'min:0.01'],
            '*.sale_price' => ['required', 'numeric', 'min:0.05', 'gte:*.purchase_price'],
            '*.stock' => ['nullable', 'integer', 'min:0'],
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
        $data = $this->all();
        
        foreach ($data as $key => $item) {
            if (isset($item['name'])) {
                $data[$key]['name'] = Str::headline($item['name']);
            }
            if (isset($item['description'])) {
                $data[$key]['description'] = !empty($item['description']) 
                    ? Str::of($item['description'])->trim()->ucfirst()->toString()
                    : null;
            }
            if (isset($item['purchase_price'])) {
                $data[$key]['purchase_price'] = round((float) $item['purchase_price'], 2);
            }
            if (isset($item['sale_price'])) {
                $data[$key]['sale_price'] = round((float) $item['sale_price'], 2);
            }
            if (isset($item['stock'])) {
                $data[$key]['stock'] = (int) $item['stock'];
            }
            if (isset($item['status'])) {
                $data[$key]['status'] = (bool) $item['status'];
            }
        }

        $this->merge($data);
    }
}
