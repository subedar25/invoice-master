<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Timesheet;
use App\Models\User;
use OwenIt\Auditing\Models\Audit;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TimesheetAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_timesheet_creation_is_audited()
    {
        $user = User::factory()->create();

        $timesheet = Timesheet::create([
            'user_id' => $user->id,
            'start_time' => now(),
            'clock_in_mode' => 'office',
            'type' => 'normal_paid',
            //  'notes' => '',
        ]);

        $audit = Audit::where('auditable_type', Timesheet::class)
                      ->where('auditable_id', $timesheet->id)
                      ->where('event', 'created')
                      ->first();

        $this->assertNotNull($audit);
        $this->assertEquals('created', $audit->event);
    }

    public function test_timesheet_update_is_audited()
    {
        $user = User::factory()->create();
        $timesheet = Timesheet::factory()->create(['user_id' => $user->id]);

        $timesheet->update(['notes' => 'Updated notes']);

        $audit = Audit::where('auditable_type', Timesheet::class)
                      ->where('auditable_id', $timesheet->id)
                      ->where('event', 'updated')
                      ->first();

        $this->assertNotNull($audit);
        $this->assertEquals('updated', $audit->event);
    }

    public function test_timesheet_soft_delete_is_audited()
    {
        $user = User::factory()->create();
        $timesheet = Timesheet::factory()->create(['user_id' => $user->id]);

        $timesheet->delete();

        $audit = Audit::where('auditable_type', Timesheet::class)
                      ->where('auditable_id', $timesheet->id)
                      ->where('event', 'deleted')
                      ->first();

        $this->assertNotNull($audit);
        $this->assertEquals('deleted', $audit->event);
    }

    public function test_timesheet_restore_is_audited()
    {
        $user = User::factory()->create();
        $timesheet = Timesheet::factory()->create(['user_id' => $user->id]);

        $timesheet->delete();
        $timesheet->restore();

        $audit = Audit::where('auditable_type', Timesheet::class)
                      ->where('auditable_id', $timesheet->id)
                      ->where('event', 'restored')
                      ->first();

        $this->assertNotNull($audit);
        $this->assertEquals('restored', $audit->event);
    }
}
