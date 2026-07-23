<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority_id' => ['required', 'uuid', 'exists:priorities,id'],
            'category_id' => ['required', 'uuid', 'exists:categories,id'],

            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => [
                'file',
                'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx',
                'max:2048'
            ],
        ];

        if ($this->user()->hasAnyRole(['administrator', 'supervisor'])) {
            $rules['assigned_agent_id'] = ['nullable', 'uuid', 'exists:users,id'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'attachments.*.mimes' => 'The file must be of type: jpg, jpeg, png, pdf, doc, docx, xls, xlsx.',
            'attachments.*.max' => 'The maximum file size is 2 MB.',
        ];
    }
}
