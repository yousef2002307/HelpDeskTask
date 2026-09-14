<?php

namespace App\Http\Requests\Shared;

use Illuminate\Foundation\Http\FormRequest;

class EscalateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:1000'],
            'escalated_by' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'reason' => [
                'description' => 'Optional business reason explaining the need for escalation.',
                'example' => 'Production payment gateway SLA breached. VIP customer affected.',
            ],
            'escalated_by' => [
                'description' => 'Optional User/Agent ID performing the escalation. If authenticated via Sanctum, defaults to the authenticated user ID.',
                'example' => 1,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.max' => 'The escalation reason cannot exceed 1000 characters.',
            'escalated_by.exists' => 'The specified agent does not exist.',
        ];
    }
}
