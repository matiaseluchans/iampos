<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Services\TimeCalculationService;
use App\Services\CapacityService;
use App\Repositories\ReservationRepository;
use App\Http\Requests\StoreReservationRequest;

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

    public function update($request, $reservation)
    {
        return $this->repository->update($request, $reservation);
    }

    public function delete($id)
    {
        return $this->repository->delete($id);
    }

    public function changestatus(Request $request, $id)
    {
        return $this->repository->changestatus($request, $id);
    }

    /*public function index(Request $request)
    {
        $query = Reservation::with(['user', 'serviceType', 'resource']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('service_type_id')) {
            $query->where('service_type_id', $request->service_type_id);
        }

        if ($request->has('resource_id')) {
            $query->where('resource_id', $request->resource_id);
        }

        if ($request->has('upcoming')) {
            $query->where('start_time', '>', now());
        }

        if ($request->has('past')) {
            $query->where('end_time', '<', now());
        }

        if ($request->has('date')) {
            $query->whereDate('start_time', $request->date);
        }

        $reservations = $query->orderBy('start_time', 'desc')->paginate(20);

        return response()->json($reservations);
    }

    public function show(Reservation $reservation)
    {
        $reservation->load(['user', 'serviceType', 'resource', 'quotations', 'payments']);
        return response()->json($reservation);
    }*/    

    public function destroy($reservation)
    {
        return $this->repository->destroy($reservation);
    }

    

    public function confirm($reservation)
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

    public function quotations($reservation)
    {
        return $this->repository->quotations($reservation);
    }

    public function payments($reservation)
    {
        return $this->repository->payments($reservation);
    }
}