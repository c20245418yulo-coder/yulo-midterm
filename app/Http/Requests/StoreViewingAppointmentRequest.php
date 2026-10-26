<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreViewingAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'property_id' => ['required', 'exists:properties,id'],
            'agent_id' => ['required', 'exists:agents,id'],
            'viewing_date' => ['required', 'date', 'after_or_equal:today'],
            'viewing_time' => [
                'required',
                'date_format:H:i',
                Rule::unique('viewing_appointments', 'viewing_time')
                    ->where('agent_id', $this->input('agent_id'))
                    ->where('viewing_date', $this->input('viewing_date')),
            ],
            'client_feedback' => ['nullable', 'string'],
            'viewing_status' => ['sometimes', 'in:Scheduled,Completed,Cancelled'],
        ];
    }
}
