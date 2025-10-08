<?php

namespace App\Services;

use App\Models\PricingRule;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PricingService
{
    public function calculatePrice(Reservation $reservation)
    {
        $serviceType = $reservation->serviceType;
        $basePrice = $serviceType->base_price;
        
        // Calcular precio basado en unidades de tiempo
        $baseTotal = $basePrice * $reservation->time_units;
        
        // Aplicar reglas de precios
        $finalPrice = $this->applyPricingRules($baseTotal, $reservation);
        
        return $finalPrice;
    }
    
    protected function applyPricingRules($basePrice, Reservation $reservation)
    {
        $rules = PricingRule::where('service_type_id', $reservation->service_type_id)
            ->orWhereNull('service_type_id') // Reglas globales
            ->active()
            ->orderBy('priority', 'desc')
            ->get();
            
        $finalPrice = $basePrice;
        
        foreach ($rules as $rule) {
            if ($this->evaluateRuleConditions($rule, $reservation)) {
                $finalPrice = $this->applyRuleModification($rule, $finalPrice);
                Log::info('Regla aplicada', [
                    'rule_id' => $rule->id,
                    'rule_name' => $rule->name,
                    'price_before' => $basePrice,
                    'price_after' => $finalPrice
                ]);
            }
        }
        
        return $finalPrice;
    }
    
    protected function evaluateRuleConditions(PricingRule $rule, Reservation $reservation)
    {
        $conditions = $this->parseConditions($rule->conditions);
        
        if (empty($conditions)) {
            Log::warning('Regla sin condiciones válidas', ['rule_id' => $rule->id]);
            return false;
        }
        
        foreach ($conditions as $condition) {
            if (!$this->evaluateSingleCondition($condition, $reservation)) {
                return false;
            }
        }
        
        return true;
    }
    
    protected function parseConditions($conditions)
    {
        if (is_array($conditions)) {
            return $conditions;
        }
        
        if (is_string($conditions) && !empty($conditions)) {
            $decoded = json_decode($conditions, true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            } else {
                Log::error('Error decodificando JSON de condiciones', [
                    'conditions' => $conditions,
                    'error' => json_last_error_msg()
                ]);
            }
        }
        
        return [];
    }
    
    protected function evaluateSingleCondition(array $condition, Reservation $reservation)
    {
        if (!isset($condition['type'])) {
            Log::warning('Condición sin tipo', ['condition' => $condition]);
            return false;
        }
        
        try {
            switch ($condition['type']) {
                case 'day_of_week':
                    return $this->evaluateDayOfWeek($condition, $reservation);
                    
                case 'time_range':
                    return $this->evaluateTimeRange($condition, $reservation);
                    
                case 'participants':
                    return $this->evaluateParticipants($condition, $reservation);
                    
                case 'date_range':
                    return $this->evaluateDateRange($condition, $reservation);
                    
                case 'dog_weight':
                    return $this->evaluateDogWeight($condition, $reservation);
                    
                case 'multiple_pets':
                    return $this->evaluateMultiplePets($condition, $reservation);
                    
                default:
                    Log::warning('Tipo de condición no soportado', [
                        'type' => $condition['type'],
                        'condition' => $condition
                    ]);
                    return false;
            }
        } catch (\Exception $e) {
            Log::error('Error evaluando condición', [
                'condition' => $condition,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    protected function evaluateDayOfWeek(array $condition, Reservation $reservation)
    {
        if (!isset($condition['value']) || !is_array($condition['value'])) {
            return false;
        }
        
        $dayOfWeek = $reservation->start_time->dayOfWeek; // 0 (domingo) a 6 (sábado)
        return in_array($dayOfWeek, $condition['value']);
    }
    
    protected function evaluateTimeRange(array $condition, Reservation $reservation)
    {
        if (!isset($condition['value']) || !is_array($condition['value']) || count($condition['value']) !== 2) {
            return false;
        }
        
        $startTime = $reservation->start_time->format('H:i');
        return $startTime >= $condition['value'][0] && $startTime <= $condition['value'][1];
    }
    
    protected function evaluateParticipants(array $condition, Reservation $reservation)
    {
        if (!isset($condition['value']) || !is_array($condition['value']) || count($condition['value']) !== 2) {
            return false;
        }
        
        $participants = $reservation->participants_count;
        return $participants >= $condition['value'][0] && $participants <= $condition['value'][1];
    }
    
    protected function evaluateDateRange(array $condition, Reservation $reservation)
    {
        if (!isset($condition['value']) || !is_array($condition['value']) || count($condition['value']) !== 2) {
            return false;
        }
        
        $reservationDate = $reservation->start_time->format('m-d');
        return $reservationDate >= $condition['value'][0] && $reservationDate <= $condition['value'][1];
    }
    
    protected function evaluateDogWeight(array $condition, Reservation $reservation)
    {
        // Asumiendo que el peso del perro está en special_requirements o en una relación
        // Esta es una implementación de ejemplo - adaptar según tu modelo de datos
        $dogWeight = $this->extractDogWeightFromReservation($reservation);
        
        if ($dogWeight === null) return false;
        
        $operator = $condition['operator'] ?? '==';
        $value = $condition['value'] ?? 0;
        
        switch ($operator) {
            case '>': return $dogWeight > $value;
            case '>=': return $dogWeight >= $value;
            case '<': return $dogWeight < $value;
            case '<=': return $dogWeight <= $value;
            case '==': return $dogWeight == $value;
            default: return false;
        }
    }
    
    protected function evaluateMultiplePets(array $condition, Reservation $reservation)
    {
        $operator = $condition['operator'] ?? '==';
        $value = $condition['value'] ?? 1;
        
        // Asumiendo que participants_count representa número de mascotas
        $petCount = $reservation->participants_count;
        
        switch ($operator) {
            case '>': return $petCount > $value;
            case '>=': return $petCount >= $value;
            case '<': return $petCount < $value;
            case '<=': return $petCount <= $value;
            case '==': return $petCount == $value;
            default: return false;
        }
    }
    
    protected function applyRuleModification(PricingRule $rule, $currentPrice)
    {
        switch ($rule->modification_type) {
            case 'fixed_amount':
                return $currentPrice + $rule->modification_value;
                
            case 'percentage':
                return $currentPrice * (1 + ($rule->modification_value / 100));
                
            case 'fixed_rate':
                return $rule->modification_value;
                
            default:
                Log::warning('Tipo de modificación no soportado', [
                    'type' => $rule->modification_type,
                    'rule_id' => $rule->id
                ]);
                return $currentPrice;
        }
    }
    
    // Método helper para extraer peso del perjo (ejemplo)
    protected function extractDogWeightFromReservation(Reservation $reservation)
    {
        // Implementar según cómo almacenes el peso del perro
        // Podría estar en special_requirements, en una relación, etc.
        preg_match('/\b(\d+)kg\b/i', $reservation->special_requirements ?? '', $matches);
        return $matches[1] ?? null;
    }
}