<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'content' => ['required', 'string'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx', 'max:2048'],
        ];

        if ($this->user()->hasAnyRole(['administrator', 'supervisor', 'agent'])) {
            $rules['is_internal'] = ['boolean'];
        } else {
            $this->merge(['is_internal' => false]);
        }

        return $rules;
    }
}
