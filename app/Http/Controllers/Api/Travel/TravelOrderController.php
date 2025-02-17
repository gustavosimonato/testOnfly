<?php

namespace App\Http\Controllers\Api\Travel;

use App\Exceptions\CustomMessageException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Travel\ListTravelOrderRequest;
use App\Http\Requests\Api\Travel\StoreTravelOrderRequest;
use App\Http\Requests\Api\Travel\UpdateTravelOrderRequest;
use App\Http\Resources\Api\Travel\TravelOrderResource;
use App\Services\Travel\TravelOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class TravelOrderController extends Controller
{
    protected $travelOrderService;

    public function __construct(TravelOrderService $travelOrderService)
    {
        $this->travelOrderService = $travelOrderService;
    }

    /**
     * @param ListTravelOrderRequest $request
     * @return JsonResponse
     * @throws CustomMessageException
     */
    public function index(ListTravelOrderRequest $request): JsonResponse
    {
        $travelOrders = $this->travelOrderService->getTravelOrders($request->validated());

        return TravelOrderResource::collection($travelOrders)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @param StoreTravelOrderRequest $request
     * @return JsonResponse
     */
    public function store(StoreTravelOrderRequest $request): JsonResponse
    {
        $travelOrder = $this->travelOrderService->create($request->validated());

        return (new TravelOrderResource($travelOrder))
            ->additional(['message' => 'Travel order created'])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $travelRequest = $this->travelOrderService->find($id);

        return (new TravelOrderResource($travelRequest))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @param UpdateTravelOrderRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws CustomMessageException
     * @throws \Throwable
     */
    public function updateStatus(UpdateTravelOrderRequest $request, int $id): JsonResponse
    {
        $travelRequest = $this->travelOrderService->updateStatus($id, $request->status);

        return (new TravelOrderResource($travelRequest))
            ->additional(['message' => 'Travel order updated'])
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @param int $id
     * @return JsonResponse
     * @throws CustomMessageException
     * @throws \Throwable
     */
    public function cancel(int $id): JsonResponse
    {
        $travelRequest = $this->travelOrderService->cancel($id);

        return (new TravelOrderResource($travelRequest))
            ->additional(['message' => 'Travel order cancelled'])
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
