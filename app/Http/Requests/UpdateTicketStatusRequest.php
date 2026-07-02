<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Services\TicketStatusService;
use Illuminate\Validation\Rule;

class UpdateTicketStatusRequest extends FormRequest
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
            'status' => ['required', 'string', Rule::in(TicketStatusService::getAllStatuses())],
        ];

        if ($this->user()->hasAnyRole(['administrator', 'supervisor'])) {
            $rules['assigned_agent_id'] = ['nullable', 'uuid', 'exists:users,id'];
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->user()->hasAnyRole(['administrator', 'supervisor']) && $this->has('assigned_agent_id')) {
                $validator->errors()->add('assigned_agent_id', 'You do not have permission to assign agents.');
            }
        });
    }
}
