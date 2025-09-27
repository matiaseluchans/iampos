<?php

namespace App\Repositories;

use App\Models\PricingRule;
use App\Models\ServiceType;

class PricingRuleRepository extends  BaseRepository
{
    public function __construct(PricingRule $m)
    {
        parent::__construct($m);
    }

    public function testRule($request, $pricingRule)
    {
        $request->validate([
            'reservation_data' => 'required|array',
            'reservation_data.start_time' => 'required|date',
            'reservation_data.participants_count' => 'sometimes|integer'
        ]);

        $reservationData = $request->reservation_data;

        // Simular una reserva para probar la regla
        $reservation = new \App\Models\Reservation();
        $reservation->fill($reservationData);

        $pricingService = app(\App\Services\PricingService::class);
        
        $applies = $pricingService->evaluateRuleConditions($pricingRule, $reservation);
        $result = $pricingService->applyRule($pricingRule, 100); // Precio base de prueba

        $data = [
            'rule_applies' => $applies,
            'test_result' => $result,
            'original_price' => 100,
            'modified_price' => $result
        ];

        $this->successResponse($data);
    }

    public function serviceTypeRules(ServiceType $serviceType)
    {
        $rules = $serviceType->pricingRules()
            ->active()
            ->orderBy('priority', 'desc')
            ->get();

        return response()->json($rules);
    }
}
