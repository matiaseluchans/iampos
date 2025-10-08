<?php
// app/Observers/ReservationObserver.php
namespace App\Observers;

use App\Models\Reservation;
use App\Models\Resource;

class ReservationObserver
{
    public function created(Reservation $reservation)
    {
        if ($reservation->resource && $reservation->resource->is_shared) {
            $this->updateResourceUsage($reservation->resource, 'increment');
        }
    }
    
    public function updated(Reservation $reservation)
    {
        // Si cambia el estado a confirmed/cancelled
        if ($reservation->isDirty('status') && $reservation->resource && $reservation->resource->is_shared) {
            if ($reservation->status === 'confirmed' && $reservation->getOriginal('status') !== 'confirmed') {
                $this->updateResourceUsage($reservation->resource, 'increment');
            } elseif ($reservation->status === 'cancelled' && $reservation->getOriginal('status') === 'confirmed') {
                $this->updateResourceUsage($reservation->resource, 'decrement');
            }
        }
    }
    
    public function deleted(Reservation $reservation)
    {
        if ($reservation->resource && $reservation->resource->is_shared && $reservation->status === 'confirmed') {
            $this->updateResourceUsage($reservation->resource, 'decrement');
        }
    }
    
    protected function updateResourceUsage(Resource $resource, $action)
    {
        $reservedCapacity = $resource->reservations()
            ->where('status', 'confirmed')
            ->where(function($query) {
                $query->where('end_time', '>', now())
                      ->orWhereNull('end_time');
            })
            ->sum('required_capacity');
        
        $resource->current_usage = $reservedCapacity;
        $resource->save();
    }
}