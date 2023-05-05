<?php
    
    
    namespace App\Validations;

    use Illuminate\Support\Facades\Validator;
    use Illuminate\Validation\Rule;

    class UpdatingProductsValidation
    {
        
        public function rules()
        {
            return [
                'name' => 'required|string',
                'sku' => 'unique:products,sku',
                'price' => 'numeric',
                'quantity' => 'numeric',
            ];
        }
    
        public function messages()
        {
            return [
                'name.required' => __('name.required.key'),
                'name.string' => __('name.required.key'),
                'sku.unique' => __('sku.unique.key'),
                'price.numeric' => __('price.numeric.key'),
                'quantity.numeric' => __('quantity.numeric.key'),
            ];
        }
    
        public function validate(array $data)
        {
            return Validator::make($data, $this->rules(), $this->messages());
        }
    }