<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Reservation;

class CancelReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Verificar que el usuario puede cancelar esta reserva
        $reservation = $this->route('reservation');
        return $reservation && $reservation->status !== Reservation::STATUS_CANCELLED;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cancellation_reason' => [
                'required',
                'string',
                'max:500'
            ],
            'send_notification' => [
                'nullable',
                'boolean'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cancellation_reason.required' => 'La razón de cancelación es obligatoria.',
            'cancellation_reason.string' => 'La razón de cancelación debe ser texto.',
            'cancellation_reason.max' => 'La razón de cancelación no puede exceder los 500 caracteres.',
            
            'send_notification.boolean' => 'El campo de notificación debe ser verdadero o falso.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'cancellation_reason' => 'razón de cancelación',
            'send_notification' => 'enviar notificación',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $reservation = $this->route('reservation');
            
            if ($reservation) {
                // Verificar que la reserva no esté ya cancelada
                if ($reservation->status === Reservation::STATUS_CANCELLED) {
                    $validator->errors()->add(
                        'reservation', 
                        'Esta reserva ya está cancelada.'
                    );
                }
                
                // Verificar que la reserva no esté completada
                if ($reservation->status === Reservation::STATUS_COMPLETED) {
                    $validator->errors()->add(
                        'reservation', 
                        'No se puede cancelar una reserva completada.'
                    );
                }
                
                // Verificar que la reserva no haya comenzado
                if ($reservation->start_time->isPast()) {
                    $validator->errors()->add(
                        'reservation', 
                        'No se puede cancelar una reserva que ya ha comenzado.'
                    );
                }
            }
        });
    }
}