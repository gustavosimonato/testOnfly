<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\User;
use App\Models\TravelOrder;

class TravelOrderListingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    /**
     * Configura o ambiente para os testes
     * - Cria um usuário
     * - Autentica o usuário usando Sanctum
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    /**
     * Testa a listagem de pedidos de viagem com diferentes filtros
     * - Verifica filtro por status (aprovado)
     * - Verifica filtro por destino (Paris)
     * - Verifica filtro por período de datas
     */
    public function test_list_with_filters()
    {
        // Cria dados de teste para Nova York
        TravelOrder::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'status' => 'approved',
            'destination' => 'New York',
            'departure_date' => '2025-03-01',
            'return_date' => '2025-03-10'
        ]);

        // Cria dados de teste para Paris
        TravelOrder::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'status' => 'requested',
            'destination' => 'Paris',
            'departure_date' => '2025-04-01',
            'return_date' => '2025-04-10'
        ]);

        // Testa filtro por status
        $statusResponse = $this->getJson('/api/travel-orders?status=approved');

        $statusResponse->assertStatus(200)
            ->assertJsonCount(3, 'data');

        // Testa filtro por destino
        $destinationResponse = $this->getJson('/api/travel-orders?destination=Paris');

        $destinationResponse->assertStatus(200)
            ->assertJsonCount(2, 'data');

        // Testa filtro por período
        $dateResponse = $this->getJson('/api/travel-orders?start_date=2025-03-01&end_date=2025-03-31');

        $dateResponse->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /**
     * Testa se o usuário só consegue ver seus próprios pedidos de viagem
     * - Cria pedidos para o usuário atual
     * - Cria pedidos para outro usuário
     * - Verifica se apenas os pedidos do usuário atual são retornados
     */
    public function test_user_can_only_see_own_requests()
    {
        // Cria pedidos para o usuário atual
        TravelOrder::factory()->count(2)->create([
            'user_id' => $this->user->id
        ]);

        // Cria pedidos para outro usuário
        $otherUser = User::factory()->create();
        TravelOrder::factory()->count(3)->create([
            'user_id' => $otherUser->id
        ]);

        $response = $this->getJson('/api/travel-orders');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }
}
