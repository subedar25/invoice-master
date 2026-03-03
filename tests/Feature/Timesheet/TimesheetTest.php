<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Timesheet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;
class TimesheetTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;

protected function setUp(): void
{
    parent::setUp();

    Role::firstOrCreate([
        'name' => 'Admin',
        'guard_name' => 'web',
    ]);

    Role::firstOrCreate([
        'name' => 'Super Admin',
        'guard_name' => 'web',
    ]);

    $this->user  = User::factory()->create();
    $this->admin = User::factory()->create();

    $this->admin->assignRole('Admin'); 

    $this->actingAs($this->user);
}

    //  * CLOCK IN

public function test_user_can_clock_in()
{
    $response = $this->postJson(route('dashboard.clock-in'), [
        'clock_in_mode' => 'office',
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('timesheets', [
        'user_id' => $this->user->id,
        'end_time' => null,
    ]);
}

    public function test_user_cannot_clock_in_twice()
{
    Timesheet::factory()->open()->create([
        'user_id' => $this->user->id,
    ]);

    $response = $this->postJson(route('dashboard.clock-in'), [
        'clock_in_mode' => 'office',
    ]);

    $response->assertStatus(422);
}


    //  * CLOCK OUT

    public function test_user_can_clock_out()
{
    Timesheet::factory()->open()->create([
        'user_id' => $this->user->id,
    ]);

    $response = $this->postJson(route('dashboard.clock-out'));

    $response->assertStatus(200);

    $this->assertDatabaseMissing('timesheets', [
        'user_id' => $this->user->id,
        'end_time' => null,
    ]);
}
//end time cannot be before start time
public function test_user_cannot_clock_out_if_not_clocked_in()
{
    $response = $this->postJson(route('dashboard.clock-out'));
    $response->assertStatus(422);
}

public function test_duration_hours_is_never_negative()
{
    $ts = Timesheet::factory()->create([
        'start_time' => now(),
        'end_time'   => now(), 
    ]);

    $this->assertGreaterThanOrEqual(0, $ts->duration_hours);
}



    //  * DATA INTEGRITY

    public function test_invalid_timesheet_is_auto_healed()
    {
        $timesheet = Timesheet::factory()
            ->invalid()
            ->create();

        $timesheet->notes = 'touch';
        $timesheet->save();

        $this->assertNull($timesheet->fresh()->end_time);
        $this->assertSame(0.0, $timesheet->fresh()->duration_hours);
    }

   

    //  * ADMIN UPDATE (AJAX)

public function test_admin_can_update_timesheet_via_ajax()
{
    $timesheet = Timesheet::factory()->create();

    $this->actingAs($this->admin);

    $response = $this->patchJson(
        route('timesheets.update', $timesheet),
        [
            'notes' => 'Updated by admin',
        ]
    );

    $response->assertStatus(200);

    $this->assertDatabaseHas('timesheets', [
        'id' => $timesheet->id,
        'notes' => 'Updated by admin',
    ]);
}


    //  * CALENDAR ENDPOINT

public function test_calendar_endpoint_returns_events()
{
    Timesheet::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $response = $this->getJson(
        "/users/{$this->user->id}/timesheets/calendar"
    );

    $response->assertStatus(200);
    $response->assertJsonStructure([
        '*' => ['id', 'title', 'start'],
    ]);
}

public function test_user_sees_only_own_timesheets()
{
    Timesheet::factory()->create([
        'user_id' => $this->user->id,
    ]);

    Timesheet::factory()->create(); 

    $response = $this->getJson(
        "/users/{$this->user->id}/timesheets/calendar"
    );

    $response->assertStatus(200);

    $this->assertCount(1, $response->json());
}

}
