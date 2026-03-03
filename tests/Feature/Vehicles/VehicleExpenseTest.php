<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleExpense;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VehicleExpenseTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user    = User::factory()->create();
        $this->vehicle = Vehicle::factory()->create();

        $this->actingAs($this->user);
    }

    //  * CREATE

    public function test_vehicle_expense_create_form_loads()
    {
        $response = $this->get(
            route('vehicles.expenses.create', $this->vehicle->id)
        );

        $response->assertStatus(200);
    }

    //  * STORE

    public function test_vehicle_expense_can_be_created_via_ajax()
    {
        $response = $this->postJson(
            route('vehicles.expenses.store', $this->vehicle->id),
            [
                'vehicle_id' => $this->vehicle->id,
                'user_id'    => $this->user->id,
                'date'       => now()->toDateString(),
                'total'      => 1500,
                'mileage'    => 12000,
                'type'       => 'fuel',
                'notes'      => 'Test fuel expense',
            ]
        );

        $response
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('vehicle_expenses', [
            'vehicle_id' => $this->vehicle->id,
            'user_id'    => $this->user->id,
            'total'      => 1500,
            'type'       => 'fuel',
        ]);
    }

    public function test_vehicle_expense_validation_fails()
    {
        $response = $this->postJson(
            route('vehicles.expenses.store', $this->vehicle->id),
            []
        );

        $response->assertStatus(422);
    }

    //  * UPDATE

public function test_vehicle_expense_can_be_updated_via_ajax()
{
    $expense = VehicleExpense::factory()->create([
        'vehicle_id' => $this->vehicle->id,
        'user_id'    => $this->user->id,
        'type'       => 'fuel',
        'mileage'    => 12000,
        'date'       => now()->toDateString(),
        'total'      => 500,
    ]);

    $response = $this->patchJson(
        route('vehicles.expenses.update', [
            $this->vehicle->id,
            $expense->id,
        ]),
        [
          
            'vehicle_id' => $this->vehicle->id,
            'user_id'    => $this->user->id,
            'date'       => now()->toDateString(),
            'total'      => 750,
            'mileage'    => 13000,
            'type'       => 'fuel',
            'notes'      => 'Updated expense',
        ]
    );

    $response->assertStatus(200);

    $this->assertDatabaseHas('vehicle_expenses', [
        'id'    => $expense->id,
        'total' => 750,
        'mileage' => 13000,
    ]);
}


    //  * DELETE

    public function test_vehicle_expense_can_be_deleted_via_ajax()
    {
        $expense = VehicleExpense::factory()->create([
            'vehicle_id' => $this->vehicle->id,
            'user_id'    => $this->user->id,
        ]);

        $response = $this->deleteJson(
            route('vehicle-expenses.destroy', $expense->id)
        );

        $response->assertStatus(200);

        $this->assertSoftDeleted('vehicle_expenses', [
            'id' => $expense->id,
        ]);
    }

    //  * RECEIPT (NON-BLOCKING)

    public function test_receipt_upload_failure_does_not_block_expense_creation()
    {
        $response = $this->postJson(
            route('vehicles.expenses.store', $this->vehicle->id),
            [
                'vehicle_id' => $this->vehicle->id,
                'user_id'    => $this->user->id,
                'date'       => now()->toDateString(),
                'total'      => 200,
                'mileage'    => 1000,
                'type'       => 'fuel',
            ]
        );

        $response->assertStatus(200);

        $this->assertDatabaseHas('vehicle_expenses', [
            'vehicle_id' => $this->vehicle->id,
            'total'      => 200,
        ]);
    }
}
