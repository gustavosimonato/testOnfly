<?php

namespace App\Services\Travel;

use App\Exceptions\CustomMessageException;
use App\Models\TravelOrder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class TravelOrderService
{
    /**
     * @param array $filters
     * @return LengthAwarePaginator
     * @throws CustomMessageException
     */
    public function getTravelOrders(array $filters): LengthAwarePaginator
    {
        $sort = $filters['sort'] ?? 'departure_date';
        $sortOption = mb_strtolower($filters['sort_option'] ?? 'desc');

        if (!in_array($sortOption, ['asc', 'desc'])) {
            $sortOption = 'asc';
        }

        if (!Schema::hasColumn('travel_orders', $sort)) {
            throw new CustomMessageException('Sort parameter not found!');
        }

        $perPage = isset($filters['per_page']) ? intval($filters['per_page']) : 10;

        $travelOrders = TravelOrder::where('user_id', Auth::id())
            ->when(isset($filters['status']), fn($query) => $query->where('status', $filters['status']))
            ->when(isset($filters['destination']), fn($query) => $query->where('destination', 'like', "%{$filters['destination']}%"))
            ->when(isset($filters['start_date']) && isset($filters['end_date']), function ($query) use ($filters) {
                $query->where(function ($query) use ($filters) {
                    $query->whereBetween('departure_date', [$filters['start_date'], $filters['end_date']])
                        ->orWhereBetween('return_date', [$filters['start_date'], $filters['end_date']]);
                });
            })
            ->orderBy($sort, $sortOption)
            ->paginate($perPage);

        return $travelOrders;
    }

    /**
     * @param array $data
     * @return TravelOrder
     */
    public function create(array $data): TravelOrder
    {
        $travelOrder = TravelOrder::create([
            'user_id' => Auth::id(),
            'destination' => $data['destination'],
            'departure_date' => $data['departure_date'],
            'return_date' => $data['return_date'],
            'status' => 'requested',
        ]);

        return $travelOrder;
    }

    /**
     * @param int $id
     * @return TravelOrder
     */
    public function find(int $id): TravelOrder
    {
        $travelOrder = TravelOrder::where('user_id', Auth::id())
            ->findOrFail($id);

        return $travelOrder;
    }

    /**
     * @param int $id
     * @param string $status
     * @return TravelOrder
     * @throws CustomMessageException
     * @throws Throwable
     */
    public function updateStatus(int $id, string $status)
    {
        try {
            DB::beginTransaction();
            $travelOrder = TravelOrder::findOrFail($id);

            if ($travelOrder->user_id === Auth::id()) {
                throw new CustomMessageException('Users cannot update their own travel request status');
            }

            $travelOrder->update([
                'status' => $status,
            ]);

            DB::commit();

            // Send email notification
            $travelOrder->user->notify(new \App\Notifications\TravelOrderStatusChanged($travelOrder));

            return $travelOrder;
        } catch (Throwable $throw) {
            DB::rollBack();
            throw $throw;
        }
    }

    /**
     * @param int $id
     * @return TravelOrder
     * @throws CustomMessageException
     * @throws Throwable
     */
    public function cancel(int $id): TravelOrder
    {
        try {
            DB::beginTransaction();
            $travelOrder = TravelOrder::findOrFail($id);

            // Do not allow cancellation if the travel request has already been cancelled
            if ($travelOrder->status == 'cancelled') {
                throw new CustomMessageException('This travel request has already been cancelled');
            }

            // Do not allow cancellation if the departure date is in less than 24 hours
            $hours = now()->diffInHours($travelOrder->departure_date) >= 24;
            if (!$hours) {
                throw new CustomMessageException('Travel request cannot be cancelled less than 24 hours before departure');
            }

            $travelOrder->update([
                'status' => 'cancelled',
            ]);

            DB::commit();

            // Send email notification
            $travelOrder->user->notify(new \App\Notifications\TravelOrderStatusChanged($travelOrder));

            return $travelOrder;
        } catch (Throwable $throw) {
            DB::rollBack();
            throw $throw;
        }
    }
}
