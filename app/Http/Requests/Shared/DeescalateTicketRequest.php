<?php

namespace App\Http\Requests\Shared;

use Illuminate\Foundation\Http\FormRequest;

class DeescalateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:1000'],
            'deescalated_by' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'reason' => [
                'description' => 'Optional reason explaining the de-escalation decision.',
                'example' => 'Incident resolved by Tier 1 on-call engineer; returning to standard queue.',
            ],
            'deescalated_by' => [
                'description' => 'Optional User/Agent ID performing the de-escalation. If authenticated via Sanctum, defaults to the authenticated user ID.',
                'example' => 1,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.max' => 'The de-escalation reason cannot exceed 1000 characters.',
            'deescalated_by.exists' => 'The specified agent does not exist.',
        ];
    }
}
