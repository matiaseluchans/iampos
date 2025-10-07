<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Services\TimeCalculationService;
use App\Services\CapacityService;
use App\Repositories\ReservationRepository;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Models\Reservation;
use App\Http\Requests\CancelReservationRequest;

class ReservationController extends Controller
{
    protected $timeService;
    protected $capacityService;
    private $repository;

    public function __construct(ReservationRepository $repository, TimeCalculationService $timeService, CapacityService $capacityService)
    {
        $this->repository = $repository;
        $this->timeService = $timeService;
        $this->capacityService = $capacityService;
    }    

    public function index(Request $request)
    {
        return $this->repository->index($request);
    }

    public function show($id)
    {
        return $this->repository->get($id);
    }

    public function store(StoreReservationRequest $request)
    {
        return $this->repository->store($request);
    }

    public function update(UpdateReservationRequest $request, Reservation $reservation)
    {
        return $this->repository->updateReservation($request, $reservation);
    }

    public function delete($id)
    {
        return $this->repository->delete($id);
    }

    public function changestatus(Request $request, $id)
    {
        return $this->repository->changestatus($request, $id);
    }

    public function cancel(CancelReservationRequest $request, Reservation $reservation)
    {
        return $this->repository->cancel($request, $reservation);
    }

    public function updateTime(Request $request, Reservation $reservation)
    {
        return $this->repository->updateTime($request, $reservation);
    }     

    public function destroy(Reservation $reservation)
    {
        return $this->repository->destroy($reservation);
    }
    
    public function confirm(Reservation $reservation)
    {
        return $this->repository->confirm($reservation);
    }

    public function checkAvailability(Request $request)
    {
        return $this->repository->checkAvailability($request);
    }

    public function calculateEndTime(Request $request)
    {
        return $this->repository->calculateEndTime($request);
    }

    public function quotations(Reservation $reservation)
    {
        return $this->repository->quotations($reservation);
    }

    public function payments(Reservation $reservation)
    {
        return $this->repository->payments($reservation);
    }
}