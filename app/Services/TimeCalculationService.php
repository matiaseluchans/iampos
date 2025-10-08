<?php
// app/Services/TimeCalculationService.php
namespace App\Services;

use App\Models\ServiceType;
use Carbon\Carbon;

class TimeCalculationService
{
    public function calculateEndTime(ServiceType $serviceType, $startTime, $timeUnits)
    {
        $start = Carbon::parse($startTime);
        
        switch ($serviceType->time_unit) {
            case 'minutes':
                return $start->copy()->addMinutes($serviceType->duration_minutes * $timeUnits);
            
            case 'hours':
                return $start->copy()->addHours($timeUnits);
            
            case 'days':
                // Para hoteles: check-in a las 15:00, check-out a las 12:00
                $endDate = $start->copy()->addDays($timeUnits);
                return $endDate->setTime(12, 0); // Check-out a las 12:00
            
            default:
                return $start->copy()->addHours($timeUnits);
        }
    }
    
    public function calculateTimeUnits(ServiceType $serviceType, $startTime, $endTime)
    {
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);
        
        switch ($serviceType->time_unit) {
            case 'minutes':
                $diffMinutes = $end->diffInMinutes($start);
                return ceil($diffMinutes / $serviceType->duration_minutes);
            
            case 'hours':
                return $end->diffInHours($start);
            
            case 'days':
                // Para hoteles: considerar check-in/check-out times
                return $this->calculateHotelDays($start, $end);
            
            default:
                return $end->diffInHours($start);
        }
    }
    
    protected function calculateHotelDays($checkIn, $checkOut)
    {
        $checkInTime = $checkIn->copy()->setTime(15, 0); // Check-in a las 15:00
        $checkOutTime = $checkOut->copy()->setTime(12, 0); // Check-out a las 12:00
        
        // Si el check-in es antes de las 15:00, contar como día completo
        if ($checkIn->lt($checkInTime)) {
            $checkIn = $checkInTime->copy()->subDay();
        }
        
        return $checkOutTime->diffInDays($checkIn);
    }
    
    public function validateTimeUnits(ServiceType $serviceType, $timeUnits)
    {
        if ($timeUnits < $serviceType->min_units) {
            return false;
        }
        
        if ($serviceType->max_units && $timeUnits > $serviceType->max_units) {
            return false;
        }
        
        return true;
    }
}