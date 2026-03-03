<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VehicleModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    //  * INDEX

    public function test_vehicle_index_page_loads()
    {
        $this->get('/vehicles')
            ->assertStatus(200)
            ->assertViewIs('vehicles.index');
    }

    //  * CREATE (AJAX)

    public function test_vehicle_can_be_created_via_ajax()
    {
        $payload = [
            'vin' => '1GCWCFF0H13000001',
            'description' => 'Test vehicle',
            'active' => 1,
            'hitch' => 0,
            'driver_side_sponsor' => 'Driver sponsor',
            'passenger_side_sponsor' => 'Passenger sponsor',
        ];

        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
        ])->postJson('/vehicles', $payload);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('vehicles', [
            'vin' => '1GCWCFF0H13000001',
            'deleted_at' => null,
        ]);
    }

    //  * UPDATE 

    public function test_vehicle_can_be_updated_via_ajax()
    {
        $vehicle = Vehicle::factory()->create();

        $payload = [
            'vin' => '1GCWCFF0H13000002',
            'description' => 'Updated description',
            'active' => 1,
            'hitch' => 1,
            'driver_side_sponsor' => 'Updated DS',
            'passenger_side_sponsor' => 'Updated PS',
        ];

        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
        ])->postJson(
            "/vehicles/{$vehicle->id}",
            array_merge($payload, ['_method' => 'PUT'])
        );

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'vin' => '1GCWCFF0H13000002',
        ]);
    }

    //  * INLINE EDIT

    public function test_vehicle_inline_edit_updates_single_field()
    {
        $vehicle = Vehicle::factory()->create(['active' => 0]);

        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
        ])->postJson(
            "/vehicles/{$vehicle->id}",
            [
                '_method' => 'PUT',
                'active' => 1,
            ]
        );

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'field' => 'active',
                     'value' => 1,
                 ]);

        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'active' => 1,
        ]);
    }

    //  * DUPLICATE

    public function test_vehicle_can_be_duplicated()
    {
        $vehicle = Vehicle::factory()->create();

        $payload = $vehicle->only([
            'vin',
            'description',
            'active',
            'hitch',
            'driver_side_sponsor',
            'passenger_side_sponsor',
        ]);

        $payload['vin'] = '1GCWCFF0H13000999';

        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
        ])->postJson("/vehicles/{$vehicle->id}/duplicate", $payload);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('vehicles', [
            'vin' => '1GCWCFF0H13000999',
        ]);
    }

    //  * DELETE (SOFT DELETE)

    public function test_vehicle_can_be_deleted()
    {
        $vehicle = Vehicle::factory()->create();

        $this->deleteJson("/vehicles/{$vehicle->id}")
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertSoftDeleted('vehicles', [
            'id' => $vehicle->id,
        ]);
    }

    //  * BULK DELETE (SOFT DELETE)

    public function test_bulk_delete_vehicles()
    {
        $vehicles = Vehicle::factory()->count(3)->create();
        $ids = $vehicles->pluck('id')->toArray();

        $this->deleteJson('/vehicles/bulk-delete', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('vehicles', ['id' => $id]);
        }
    }

    //  * INFO PAGE

    public function test_vehicle_info_page_loads()
    {
        $vehicle = Vehicle::factory()->create();

        $this->get("/entity/vehicles/{$vehicle->id}")
             ->assertStatus(200)
             ->assertSee($vehicle->vin);
    }

    //  * EXPENSES TAB

    public function test_vehicle_expenses_tab_does_not_error_when_empty()
    {
        $vehicle = Vehicle::factory()->create();

        $this->get("/entity/vehicles/{$vehicle->id}?tab=expenses")
             ->assertStatus(200)
             ->assertSee('Vehicle Expenses');
    }
}
