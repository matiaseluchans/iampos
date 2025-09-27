<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReservationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $baseRules = [
            'customer_id' => 'required|exists:customers,id',
            'service_type_id' => 'required|exists:service_types,id',
            'start_time' => 'required|date|after_or_equal:now',
            'time_units' => 'required|integer|min:1',
            'required_capacity' => 'required|integer|min:1',
            'participants_count' => 'required|integer|min:1',
            'features' => 'sometimes|array',
            'features.*' => 'nullable',
            'special_requirements' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:500'
        ];

        // Agregar regla condicional para resource_id
        $baseRules['resource_id'] = $this->getResourceIdRule();

        return $baseRules;
    }

    /**
     * Obtener la regla de validación condicional para resource_id
     */
    protected function getResourceIdRule()
    {
        return [
            'nullable',
            'exists:resources,id',
            Rule::requiredIf(function () {
                return $this->isResourceRequired();
            }),
            function ($attribute, $value, $fail) {
                // Validación adicional: verificar que el recurso sea del tipo correcto
                if ($value && !$this->isValidResourceForService()) {
                    $fail('El recurso seleccionado no es compatible con este servicio.');
                }
            }
        ];
    }

    /**
     * Determinar si el resource_id es requerido basado en el servicio
     */
    protected function isResourceRequired(): bool
    {
        $serviceTypeId = $this->service_type_id;
        
        if (!$serviceTypeId) {
            return false;
        }

        $serviceType = \App\Models\ServiceType::find($serviceTypeId);
        
        return $serviceType && $serviceType->requires_resource;
    }

    /**
     * Validar que el recurso sea del tipo correcto para el servicio
     */
    protected function isValidResourceForService(): bool
    {
        $serviceTypeId = $this->service_type_id;
        $resourceId = $this->resource_id;
        
        if (!$serviceTypeId || !$resourceId) {
            return true;
        }

        $serviceType = \App\Models\ServiceType::find($serviceTypeId);
        $resource = \App\Models\Resource::find($resourceId);

        if (!$serviceType || !$resource) {
            return false;
        }

        // Verificar que el recurso sea del tipo requerido por el servicio
        return $serviceType->resource_type_id === $resource->resource_type_id;
    }

    public function messages()
    {
        return [
            'customer_id.required' => 'El cliente es requerido',
            'customer_id.exists' => 'El cliente seleccionado no existe',
            'service_type_id.required' => 'El tipo de servicio es requerido',
            'service_type_id.exists' => 'El tipo de servicio seleccionado no existe',
            'resource_id.required' => 'El recurso es requerido para este servicio',
            'resource_id.exists' => 'El recurso seleccionado no existe',
            'start_time.after_or_equal' => 'La fecha de inicio debe ser igual o posterior a la fecha actual',
            'time_units.min' => 'Debe reservar al menos una unidad de tiempo',
            'required_capacity.min' => 'La capacidad requerida debe ser al menos 1',
            'participants_count.min' => 'Debe haber al menos un participante'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$validator->errors()->any()) {
                $this->validateResourceAvailability($validator);
                $this->validateServiceTypeConstraints($validator);
            }
        });
    }

    /**
     * Validar disponibilidad del recurso
     */
    protected function validateResourceAvailability($validator)
    {
        $serviceTypeId = $this->service_type_id;
        $resourceId = $this->resource_id;
        $startTime = $this->start_time;
        
        if (!$serviceTypeId || !$resourceId || !$startTime) {
            return;
        }

        $serviceType = \App\Models\ServiceType::find($serviceTypeId);
        $resource = \App\Models\Resource::find($resourceId);

        if (!$serviceType || !$resource) {
            return;
        }

        // Calcular end_time (necesitarías tu TimeCalculationService aquí)
        $endTime = $this->calculateEndTime($serviceType, $startTime);

        // Verificar disponibilidad
        if (!$resource->availability($startTime, $endTime, $this->required_capacity)) {
            $validator->errors()->add(
                'resource_id',
                'El recurso seleccionado no está disponible para el horario solicitado.'
            );
        }
    }

    /**
     * Validar restricciones del tipo de servicio
     */
    protected function validateServiceTypeConstraints($validator)
    {
        $serviceTypeId = $this->service_type_id;
        
        if (!$serviceTypeId) {
            return;
        }

        $serviceType = \App\Models\ServiceType::find($serviceTypeId);
        
        if (!$serviceType) {
            return;
        }

        // Validar unidades de tiempo
        if ($this->time_units < $serviceType->min_units) {
            $validator->errors()->add(
                'time_units',
                "El servicio requiere un mínimo de {$serviceType->min_units} unidades."
            );
        }

        if ($serviceType->max_units && $this->time_units > $serviceType->max_units) {
            $validator->errors()->add(
                'time_units',
                "El servicio permite un máximo de {$serviceType->max_units} unidades."
            );
        }

        // Validar participantes
        if ($serviceType->max_participants && $this->participants_count > $serviceType->max_participants) {
            $validator->errors()->add(
                'participants_count',
                "El servicio permite un máximo de {$serviceType->max_participants} participantes."
            );
        }
    }

    /**
     * Calcular end_time (simplificado - deberías usar tu servicio)
     */
    protected function calculateEndTime($serviceType, $startTime)
    {
        // Esta es una implementación simplificada
        // Deberías usar tu TimeCalculationService aquí
        $start = \Carbon\Carbon::parse($startTime);
        $minutes = $serviceType->duration_minutes * $this->time_units;
        
        return $start->addMinutes($minutes)->toDateTimeString();
    }

    public function prepareForValidation()
    {
        $this->merge([
            'time_units' => (int) $this->time_units,
            'required_capacity' => (int) $this->required_capacity,
            'participants_count' => (int) $this->participants_count,
            'resource_id' => $this->resource_id ? (int) $this->resource_id : null,
            'features' => $this->features ?? [],
        ]);
    }
}