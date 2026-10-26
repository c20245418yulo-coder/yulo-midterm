<?php

namespace App\Http\Requests;

use App\Models\ViewingAppointment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateViewingAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $scheduleFields = ['agent_id', 'viewing_date', 'viewing_time'];

        if (! collect($scheduleFields)->contains(
            fn (string $field): bool => $this->exists($field)
        )) {
            return;
        }

        $routeAppointment = $this->route('viewing_appointment') ?? $this->route('id');
        $appointment = $routeAppointment instanceof ViewingAppointment
            ? $routeAppointment
            : ViewingAppointment::find($routeAppointment);

        if ($appointment === null) {
            return;
        }

        $this->merge([
            'agent_id' => $this->input('agent_id', $appointment->agent_id),
            'viewing_date' => $this->input('viewing_date', $appointment->viewing_date->toDateString()),
            'viewing_time' => $this->exists('viewing_time')
                ? $this->input('viewing_time')
                : substr((string) $appointment->viewing_time, 0, 5),
        ]);
    }

    public function rules(): array
    {
        $routeAppointment = $this->route('viewing_appointment') ?? $this->route('id');
        $appointmentId = $routeAppointment instanceof ViewingAppointment
            ? $routeAppointment->getKey()
            : $routeAppointment;

        return [
            'client_id' => ['sometimes', 'required', 'exists:clients,id'],
            'property_id' => ['sometimes', 'required', 'exists:properties,id'],
            'agent_id' => ['sometimes', 'required', 'exists:agents,id'],
            'viewing_date' => ['sometimes', 'required', 'date'],
            'viewing_time' => [
                'sometimes',
                'required',
                'date_format:H:i',
                Rule::unique('viewing_appointments', 'viewing_time')
                    ->where('agent_id', $this->input('agent_id'))
                    ->where('viewing_date', $this->input('viewing_date'))
                    ->ignore($appointmentId),
            ],
            'client_feedback' => ['nullable', 'string'],
            'viewing_status' => ['sometimes', 'in:Scheduled,Completed,Cancelled'],
        ];
    }
}
