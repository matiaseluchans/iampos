<?php

namespace App\Repositories;

use App\Models\Quotation;
use App\Models\Reservation;
use Illuminate\Http\Request;

class QuotationRepository extends BaseRepository
{
    public function __construct(Quotation $m)
    {
        parent::__construct($m);
    }

    public function accept(Quotation $quotation)
    {
        $quotation->accept();

        return response()->json([
            'message' => 'Cotización aceptada exitosamente',
            'quotation' => $quotation,
            'reservation' => $quotation->reservation
        ]);
    }

    public function reject(Request $request, Quotation $quotation)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $quotation->reject($request->reason);

        return response()->json([
            'message' => 'Cotización rechazada',
            'quotation' => $quotation
        ]);
    }

    public function markAsSent(Quotation $quotation)
    {
        $quotation->markAsSent();

        return response()->json([
            'message' => 'Cotización marcada como enviada',
            'quotation' => $quotation
        ]);
    }

    public function addItem(Request $request, Quotation $quotation)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'notes' => 'sometimes|string|max:500'
        ]);

        $item = $quotation->addItem(
            $request->description,
            $request->price,
            $request->quantity
        );

        if ($request->has('notes')) {
            $item->update(['notes' => $request->notes]);
        }

        // Recalcular total
        $quotation->calculateTotal();

        return response()->json([
            'message' => 'Ítem agregado exitosamente',
            'item' => $item,
            'quotation' => $quotation->fresh()
        ]);
    }

    public function removeItem(Quotation $quotation, $itemId)
    {
        $item = $quotation->items()->findOrFail($itemId);
        $item->delete();

        // Recalcular total
        $quotation->calculateTotal();

        return response()->json([
            'message' => 'Ítem eliminado exitosamente',
            'quotation' => $quotation->fresh()
        ]);
    }

    public function calculateTotal(Quotation $quotation)
    {
        $total = $quotation->calculateTotal();

        return response()->json([
            'total_price' => $total,
            'quotation' => $quotation->fresh()
        ]);
    }

    public function generateFromReservation(Reservation $reservation)
    {
        if ($reservation->quotations()->whereIn('status', [Quotation::STATUS_DRAFT, Quotation::STATUS_SENT])->exists()) {
            return response()->json([
                'message' => 'Ya existe una cotización activa para esta reserva'
            ], 422);
        }

        $quotation = $reservation->generateQuotation();

        return response()->json([
            'message' => 'Cotización generada exitosamente',
            'quotation' => $quotation->load('items')
        ], 201);
    }
}
