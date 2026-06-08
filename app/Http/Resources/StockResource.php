<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'quantity'          => $this->quantity,
            'reserved_quantity' => $this->reserved_quantity,
            'available'         => $this->available,
            'minimum_stock'     => $this->minimum_stock,
            'maximum_stock'     => $this->maximum_stock,
            'product'           => $this->when($this->relationLoaded('product'), fn () => [
                'id'   => $this->product->id,
                'name' => $this->product->name,
                'code' => $this->product->code,
            ]),
            'warehouse'         => $this->when($this->relationLoaded('warehouse') && $this->warehouse, fn () => [
                'id'   => $this->warehouse->id,
                'name' => $this->warehouse->name,
            ]),
        ];
    }
}
