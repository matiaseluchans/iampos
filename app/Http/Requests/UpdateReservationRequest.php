<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Reservation;

class UpdateReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Puedes ajustar la autorización según tu lógica
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $reservationId = $this->route('reservation') ? $this->route('reservation')->id : null;

        return [
            'customer_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:customers,id'
            ],
            'service_type_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:service_types,id'
            ],
            'resource_id' => [
                'nullable',
                'integer',
                'exists:resources,id'
            ],
            'status' => [
                'sometimes',
                'required',
                'string',
                Rule::in(Reservation::getStatuses())
            ],
            'start_time' => [
                'sometimes',
                'required',
                'date',
                'after_or_equal:now',
            ],
            'end_time' => [
                'sometimes',
                'required',
                'date',
                'after:start_time',
            ],
            'time_units' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
                'max:100'
            ],
            'required_capacity' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
                'max:100'
            ],
            'participants_count' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
                'max:100'
            ],
            'special_requirements' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'cancellation_reason' => [
                'nullable',
                'required_if:status,cancelled',
                'string',
                'max:500'
            ],
            'features' => [
                'nullable',
                'array'
            ],
            'features.pet_name' => [
                'nullable',
                'string',
                'max:100'
            ],
            'features.pet_breed' => [
                'nullable',
                'string',
                'max:100'
            ],
            'features.pet_weight' => [
                'nullable',
                'numeric',
                'min:0.1',
                'max:200'
            ],
            'features.pet_age' => [
                'nullable',
                'integer',
                'min:0',
                'max:30'
            ],
            'features.pet_gender' => [
                'nullable',
                'string',
                'in:male,female,unknown'
            ],
            'features.pet_notes' => [
                'nullable',
                'string',
                'max:500'
            ],
            'features.vehicle_plate' => [
                'nullable',
                'string',
                'max:20'
            ],
            'features.vehicle_model' => [
                'nullable',
                'string',
                'max:100'
            ],
            'features.vehicle_color' => [
                'nullable',
                'string',
                'max:50'
            ],
            'features.vehicle_type' => [
                'nullable',
                'string',
                'in:car,suv,van,motorcycle,truck'
            ],
            'features.adults_count' => [
                'nullable',
                'integer',
                'min:1',
                'max:10'
            ],
            'features.children_count' => [
                'nullable',
                'integer',
                'min:0',
                'max:10'
            ],
            'features.room_preference' => [
                'nullable',
                'string',
                'max:100'
            ],
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
            'customer_id.required' => 'El cliente es obligatorio.',
            'customer_id.exists' => 'El cliente seleccionado no existe.',
            
            'service_type_id.required' => 'El tipo de servicio es obligatorio.',
            'service_type_id.exists' => 'El tipo de servicio seleccionado no existe.',
            
            'resource_id.exists' => 'El recurso seleccionado no existe.',
            
            'status.required' => 'El estado es obligatorio.',
            'status.in' => 'El estado seleccionado no es válido.',
            
            'start_time.required' => 'La fecha y hora de inicio son obligatorias.',
            'start_time.date' => 'La fecha de inicio debe ser una fecha válida.',
            'start_time.after_or_equal' => 'La fecha de inicio no puede ser en el pasado.',
            
            'end_time.required' => 'La fecha y hora de fin son obligatorias.',
            'end_time.date' => 'La fecha de fin debe ser una fecha válida.',
            'end_time.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
            
            'time_units.required' => 'Las unidades de tiempo son obligatorias.',
            'time_units.integer' => 'Las unidades de tiempo deben ser un número entero.',
            'time_units.min' => 'Las unidades de tiempo deben ser al menos 1.',
            'time_units.max' => 'Las unidades de tiempo no pueden ser mayores a 100.',
            
            'required_capacity.required' => 'La capacidad requerida es obligatoria.',
            'required_capacity.integer' => 'La capacidad requerida debe ser un número entero.',
            'required_capacity.min' => 'La capacidad requerida debe ser al menos 1.',
            'required_capacity.max' => 'La capacidad requerida no puede ser mayor a 100.',
            
            'participants_count.required' => 'El número de participantes es obligatorio.',
            'participants_count.integer' => 'El número de participantes debe ser un número entero.',
            'participants_count.min' => 'El número de participantes debe ser al menos 1.',
            'participants_count.max' => 'El número de participantes no puede ser mayor a 100.',
            
            'special_requirements.max' => 'Los requisitos especiales no pueden exceder los 1000 caracteres.',
            'notes.max' => 'Las notas no pueden exceder los 1000 caracteres.',
            
            'cancellation_reason.required_if' => 'La razón de cancelación es obligatoria cuando el estado es cancelado.',
            'cancellation_reason.max' => 'La razón de cancelación no puede exceder los 500 caracteres.',
            
            'features.pet_name.max' => 'El nombre de la mascota no puede exceder los 100 caracteres.',
            'features.pet_breed.max' => 'La raza de la mascota no puede exceder los 100 caracteres.',
            'features.pet_weight.min' => 'El peso de la mascota debe ser al menos 0.1 kg.',
            'features.pet_weight.max' => 'El peso de la mascota no puede exceder los 200 kg.',
            'features.pet_age.min' => 'La edad de la mascota no puede ser negativa.',
            'features.pet_age.max' => 'La edad de la mascota no puede exceder los 30 años.',
            'features.pet_gender.in' => 'El género de la mascota debe ser macho, hembra o desconocido.',
            
            'features.vehicle_plate.max' => 'La placa del vehículo no puede exceder los 20 caracteres.',
            'features.vehicle_model.max' => 'El modelo del vehículo no puede exceder los 100 caracteres.',
            'features.vehicle_color.max' => 'El color del vehículo no puede exceder los 50 caracteres.',
            'features.vehicle_type.in' => 'El tipo de vehículo debe ser automóvil, SUV, van, motocicleta o camión.',
            
            'features.adults_count.min' => 'El número de adultos debe ser al menos 1.',
            'features.adults_count.max' => 'El número de adultos no puede exceder los 10.',
            'features.children_count.min' => 'El número de niños no puede ser negativo.',
            'features.children_count.max' => 'El número de niños no puede exceder los 10.',
            'features.room_preference.max' => 'La preferencia de habitación no puede exceder los 100 caracteres.',
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
            'customer_id' => 'cliente',
            'service_type_id' => 'tipo de servicio',
            'resource_id' => 'recurso',
            'status' => 'estado',
            'start_time' => 'fecha y hora de inicio',
            'end_time' => 'fecha y hora de fin',
            'time_units' => 'unidades de tiempo',
            'required_capacity' => 'capacidad requerida',
            'participants_count' => 'número de participantes',
            'special_requirements' => 'requisitos especiales',
            'notes' => 'notas',
            'cancellation_reason' => 'razón de cancelación',
            'features.pet_name' => 'nombre de la mascota',
            'features.pet_breed' => 'raza de la mascota',
            'features.pet_weight' => 'peso de la mascota',
            'features.pet_age' => 'edad de la mascota',
            'features.pet_gender' => 'género de la mascota',
            'features.vehicle_plate' => 'placa del vehículo',
            'features.vehicle_model' => 'modelo del vehículo',
            'features.vehicle_color' => 'color del vehículo',
            'features.vehicle_type' => 'tipo de vehículo',
            'features.adults_count' => 'número de adultos',
            'features.children_count' => 'número de niños',
            'features.room_preference' => 'preferencia de habitación',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convertir valores numéricos de features
        if ($this->has('features')) {
            $features = $this->features;
            
            // Convertir valores numéricos
            if (isset($features['pet_weight'])) {
                $features['pet_weight'] = floatval($features['pet_weight']);
            }
            
            if (isset($features['pet_age'])) {
                $features['pet_age'] = intval($features['pet_age']);
            }
            
            if (isset($features['adults_count'])) {
                $features['adults_count'] = intval($features['adults_count']);
            }
            
            if (isset($features['children_count'])) {
                $features['children_count'] = intval($features['children_count']);
            }
            
            $this->merge(['features' => $features]);
        }

        // Convertir otros valores numéricos
        $this->merge([
            'time_units' => $this->time_units ? intval($this->time_units) : null,
            'required_capacity' => $this->required_capacity ? intval($this->required_capacity) : null,
            'participants_count' => $this->participants_count ? intval($this->participants_count) : null,
        ]);
    }
}