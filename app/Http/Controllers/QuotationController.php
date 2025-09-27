<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\QuotationRepository;
use App\Models\Quotation;
use App\Models\Reservation;

class QuotationController extends Controller
{
    private $repository;

    public function __construct(QuotationRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        return $this->repository->all();
    }

    public function show($id)
    {
        return $this->repository->get($id);
    }

    public function store(Request $request)
    {
        return $this->repository->save($request);
    }

    public function update(Request $request, $id)
    {
        return $this->repository->update($request, $id);
    }

    public function destroy($id)
    {
        return $this->repository->delete($id);
    }

    public function accept(Quotation $quotation)
    {
        return $this->repository->accept($quotation);
    }

    public function reject(Request $request, Quotation $quotation)
    {
        return $this->repository->reject($request, $quotation);
    }

    public function markAsSent(Quotation $quotation)
    {
        return $this->repository->markAsSent($quotation);        
    }

    public function addItem($request, Quotation $quotation)
    {
        return $this->repository->addItem($request, $quotation);
    }

    public function removeItem(Quotation $quotation, $itemId)
    {
        return $this->repository->removeItem($quotation, $itemId);
    }

    public function calculateTotal(Quotation $quotation)
    {
        return $this->repository->calculateTotal($quotation);
    }

    public function generateFromReservation(Reservation $reservation)
    {
        return $this->repository->generateFromReservation($reservation);
    }
}