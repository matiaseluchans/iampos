<?php
// app/Services/CapacityService.php
namespace App\Services;

use App\Models\Resource;

class CapacityService
{
    public function checkCapacityAvailability($resourceId, $startTime, $endTime, $requiredCapacity = 1)
    {
        $resource = Resource::findOrFail($resourceId);
        //dd($resource);
        
        if (!$resource->is_shared) {
            return [
                'available' => $resource->availability($startTime, $endTime),
                'available_capacity' => $resource->availability($startTime, $endTime) ? 1 : 0,
                'required_capacity' => $requiredCapacity
            ];
        }
        
        $availableCapacity = $resource->getAvailableCapacity($startTime, $endTime);
        
        return [
            'available' => $availableCapacity >= $requiredCapacity,
            'available_capacity' => $availableCapacity,
            'required_capacity' => $requiredCapacity,
            'max_capacity' => $resource->capacity
        ];
    }
    
    public function getAvailableCapacityByResourceType($resourceTypeId, $startTime, $endTime)
    {
        $resources = Resource::where('resource_type_id', $resourceTypeId)
            ->where('is_active', true)
            ->get();
        
        $results = [];
        
        foreach ($resources as $resource) {
            if ($resource->is_shared) {
                $availableCapacity = $resource->getAvailableCapacity($startTime, $endTime);
                $results[] = [
                    'resource' => $resource,
                    'available_capacity' => $availableCapacity,
                    'max_capacity' => $resource->capacity
                ];
            } else {
                $available = $resource->availability($startTime, $endTime);
                $results[] = [
                    'resource' => $resource,
                    'available_capacity' => $available ? 1 : 0,
                    'max_capacity' => 1
                ];
            }
        }
        
        return $results;
    }
}