<?php

namespace Tests\Unit;

use App\Exceptions\CustomMessageException;
use App\Models\TravelOrder;
use App\Services\Travel\TravelOrderService;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class TravelOrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private $service;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TravelOrderService();
        $this->user = User::factory()->create();
        Auth::login($this->user);
    }

    // Testa a criação de uma ordem de viagem
    public function test_create_travel_order()
    {
        $data = [
            'destination' => 'New York',
            'departure_date' => '2025-03-01',
            'return_date' => '2025-03-10'
        ];

        $travelRequest = $this->service->create($data);

        $this->assertInstanceOf(TravelOrder::class, $travelRequest);
        $this->assertEquals($this->user->id, $travelRequest->user_id);
        $this->assertEquals('New York', $travelRequest->destination);
    }

    // Testa a listagem de ordens de viagem filtradas por status
    public function test_list_filters_by_status()
    {
        TravelOrder::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'status' => 'approved'
        ]);
        TravelOrder::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'status' => 'requested'
        ]);

        $result = $this->service->getTravelOrders(['status' => 'approved']);

        $this->assertEquals(3, $result->count());
    }

    // Testa a listagem de ordens de viagem filtradas por intervalo de datas
    public function test_list_filters_by_date_range()
    {
        TravelOrder::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'departure_date' => '2025-03-01',
            'return_date' => '2025-03-10'
        ]);
        TravelOrder::factory()->count(1)->create([
            'user_id' => $this->user->id,
            'departure_date' => '2025-04-01',
            'return_date' => '2025-04-10'
        ]);

        $result = $this->service->getTravelOrders([
            'start_date' => '2025-03-01',
            'end_date' => '2025-03-31'
        ]);

        $this->assertEquals(2, $result->count());
    }

    // Testa a atualização do status de uma ordem de viagem com sucesso
    public function test_update_status_success()
    {
        $travelOrder = TravelOrder::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'requested',
        ]);

        $admin = User::factory()->create();
        Auth::login($admin);

        $updatedOrder = $this->service->updateStatus($travelOrder->id, 'approved');

        $this->assertEquals('approved', $updatedOrder->status);
    }

    // Testa a exceção ao tentar atualizar o status da própria ordem de viagem
    public function test_update_status_throws_exception_for_user_updating_own_status()
    {
        $this->expectException(CustomMessageException::class);
        $this->expectExceptionMessage('Users cannot update their own travel request status');

        $travelOrder = TravelOrder::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'requested',
        ]);

        $this->service->updateStatus($travelOrder->id, 'approved');
    }

    // Testa a exceção ao tentar atualizar o status de uma ordem de viagem inválida
    public function test_update_status_throws_exception_for_invalid_travel_order()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $admin = User::factory()->create();
        Auth::login($admin);

        $this->service->updateStatus(999999999, 'approved'); // ID inexistente
    }

    // Testa o cancelamento bem-sucedido de uma ordem de viagem
    public function test_cancel_success()
    {
        $travelOrder = TravelOrder::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'requested',
            'departure_date' => now()->addDays(2),
        ]);

        $cancelledOrder = $this->service->cancel($travelOrder->id);

        $this->assertEquals('cancelled', $cancelledOrder->status);
    }

    // Testa a exceção ao tentar cancelar uma ordem de viagem já cancelada
    public function test_cancel_throws_exception_for_already_cancelled_order()
    {
        $this->expectException(CustomMessageException::class);
        $this->expectExceptionMessage('This travel request has already been cancelled');

        $travelOrder = TravelOrder::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'cancelled',
        ]);

        $this->service->cancel($travelOrder->id);
    }

    // Testa a exceção ao tentar cancelar uma ordem de viagem com data de partida em menos de 24 horas
    public function test_cancel_throws_exception_for_departure_within_24_hours()
    {
        $this->expectException(CustomMessageException::class);
        $this->expectExceptionMessage('Travel request cannot be cancelled less than 24 hours before departure');

        $travelOrder = TravelOrder::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'requested',
            'departure_date' => now()->addHours(12),
        ]);

        $this->service->cancel($travelOrder->id);
    }
}
