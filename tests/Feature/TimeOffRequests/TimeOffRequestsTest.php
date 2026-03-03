<?php

namespace Tests\Feature\TimeOffRequests;

use App\Models\TimeOffRequest;
use App\Models\User;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TimeOffRequestsTest extends TestCase
{
    use RefreshDatabase;

    /** @var User */
    protected $userWithListOnly;

    /** @var User */
    protected $userAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        $this->userWithListOnly = $this->createUserWithListPermissions();
        $this->userAdmin = $this->createUserWithAdminPermissions();
    }

    protected function seedPermissionsAndRoles(): void
    {
        $permissionNames = [
            'list-time-off-requests',
            'create-time-off-request',
            'edit-time-off-request',
            'delete-time-off-request',
            'status-time-off-request',
            'admin-time-off-requests',
        ];
        foreach ($permissionNames as $name) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['slug' => $name, 'display_name' => $name, 'guard_name' => 'web']
            );
        }
        $listRole = Role::firstOrCreate(['name' => 'time-off-list', 'guard_name' => 'web']);
        $listRole->syncPermissions([
            'list-time-off-requests',
            'create-time-off-request',
            'edit-time-off-request',
            'delete-time-off-request',
        ]);
        $adminRole = Role::firstOrCreate(['name' => 'time-off-admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions($permissionNames);
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function createUserWithListPermissions(): User
    {
        $user = User::factory()->create();
        $user->assignRole('time-off-list');
        return $user;
    }

    protected function createUserWithAdminPermissions(): User
    {
        $user = User::factory()->create();
        $user->assignRole('time-off-admin');
        return $user;
    }

    protected function validStorePayload(array $overrides = []): array
    {
        $user = $this->userWithListOnly ?? User::factory()->create();
        return array_merge([
            'user_id' => $user->id,
            'start_time' => now()->addDays(1)->format('Y-m-d H:i:s'),
            'end_time' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'paid' => false,
            'status' => 'pending',
            'notes' => null,
        ], $overrides);
    }

    protected function createTimeOffRequest(array $overrides = []): TimeOffRequest
    {
        $user = $overrides['user_id'] ?? $this->userWithListOnly->id;
        unset($overrides['user_id']);
        return TimeOffRequest::create(array_merge([
            'user_id' => $user,
            'start_time' => now()->addDays(1),
            'end_time' => now()->addDays(2),
            'paid' => false,
            'status' => 'pending',
            'notes' => null,
            'submitted' => true,
        ], $overrides));
    }

    // ---- index ----
    public function test_index_returns_403_without_list_or_admin_permission(): void
    {
        /** @var User $userNoPerm */
        $userNoPerm = User::factory()->create();
        $this->actingAs($userNoPerm)
            ->get(route('masterapp.time-off-requests.index'))
            ->assertStatus(403);
    }

    public function test_index_returns_200_with_list_permission(): void
    {
        $this->actingAs($this->userWithListOnly)
            ->get(route('masterapp.time-off-requests.index'))
            ->assertStatus(200);
    }

    public function test_index_returns_200_with_admin_permission(): void
    {
        $this->actingAs($this->userAdmin)
            ->get(route('masterapp.time-off-requests.index'))
            ->assertStatus(200);
    }

    public function test_index_returns_view_with_users_and_capabilities(): void
    {
        $response = $this->actingAs($this->userWithListOnly)
            ->get(route('masterapp.time-off-requests.index'));
        $response->assertStatus(200);
        $response->assertViewIs('masterapp.time_off_requests.index');
        $response->assertViewHas(['users', 'canCreate', 'canEdit', 'canDelete', 'canAdmin', 'canChangeStatus']);
    }

    // ---- data ----
    public function test_data_returns_403_without_list_or_admin_permission(): void
    {
        /** @var User $userNoPerm */
        $userNoPerm = User::factory()->create();
        $this->actingAs($userNoPerm)
            ->get(route('masterapp.time-off-requests.data'), ['draw' => 1, 'start' => 0, 'length' => 10])
            ->assertStatus(403);
    }

    public function test_data_returns_json_with_list_permission(): void
    {
        $response = $this->actingAs($this->userWithListOnly)
            ->get(route('masterapp.time-off-requests.data'), [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
            ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
    }

    public function test_data_accepts_filters(): void
    {
        $response = $this->actingAs($this->userAdmin)
            ->get(route('masterapp.time-off-requests.data'), [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
                'id_from' => 1,
                'id_to' => 10,
                'date_from' => '2025-01-01',
                'date_to' => '2025-12-31',
                'status' => 'pending',
            ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
    }

    // ---- store ----
    public function test_store_returns_403_without_create_permission(): void
    {
        /** @var User $userNoPerm */
        $userNoPerm = User::factory()->create();
        $userNoPerm->givePermissionTo('list-time-off-requests');
        $payload = $this->validStorePayload(['user_id' => $userNoPerm->id]);
        $this->actingAs($userNoPerm)
            ->from(route('masterapp.time-off-requests.index'))
            ->post(route('masterapp.time-off-requests.store'), $payload)
            ->assertStatus(403);
    }

    public function test_store_creates_request_and_redirects_with_success(): void
    {
        Notification::fake();
        $payload = $this->validStorePayload([
            'user_id' => $this->userWithListOnly->id,
            'start_time' => now()->addDays(1)->format('Y-m-d H:i:s'),
            'end_time' => now()->addDays(2)->format('Y-m-d H:i:s'),
        ]);
        $response = $this->actingAs($this->userWithListOnly)
            ->from(route('masterapp.time-off-requests.index'))
            ->post(route('masterapp.time-off-requests.store'), $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Time off request created successfully.');
        $this->assertDatabaseHas('time_off_requests', [
            'user_id' => $this->userWithListOnly->id,
            'status' => 'pending',
        ]);
    }

    public function test_store_non_admin_forces_user_id_to_self(): void
    {
        Notification::fake();
        $otherUser = User::factory()->create();
        $payload = $this->validStorePayload(['user_id' => $otherUser->id]);
        $this->actingAs($this->userWithListOnly)
            ->from(route('masterapp.time-off-requests.index'))
            ->post(route('masterapp.time-off-requests.store'), $payload);
        $this->assertDatabaseHas('time_off_requests', ['user_id' => $this->userWithListOnly->id]);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->userWithListOnly)
            ->from(route('masterapp.time-off-requests.index'))
            ->post(route('masterapp.time-off-requests.store'), [])
            ->assertSessionHasErrors(['user_id', 'start_time', 'end_time', 'status']);
    }

    public function test_store_validates_end_time_after_or_equal_start_time(): void
    {
        $payload = $this->validStorePayload([
            'start_time' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'end_time' => now()->addDays(1)->format('Y-m-d H:i:s'),
        ]);
        $this->actingAs($this->userWithListOnly)
            ->from(route('masterapp.time-off-requests.index'))
            ->post(route('masterapp.time-off-requests.store'), $payload)
            ->assertSessionHasErrors(['end_time']);
    }

    public function test_store_validates_status_enum(): void
    {
        $payload = $this->validStorePayload(['status' => 'invalid_status']);
        $this->actingAs($this->userWithListOnly)
            ->from(route('masterapp.time-off-requests.index'))
            ->post(route('masterapp.time-off-requests.store'), $payload)
            ->assertSessionHasErrors(['status']);
    }

    // ---- update ----
    public function test_update_returns_403_without_edit_permission(): void
    {
        /** @var User $userNoPerm */
        $userNoPerm = User::factory()->create();
        $userNoPerm->givePermissionTo('list-time-off-requests');
        $request = $this->createTimeOffRequest(['user_id' => $this->userWithListOnly->id]);
        $payload = $this->validStorePayload([
            'user_id' => $this->userWithListOnly->id,
            'start_time' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'end_time' => now()->addDays(4)->format('Y-m-d H:i:s'),
        ]);
        $this->actingAs($userNoPerm)
            ->from(route('masterapp.time-off-requests.index'))
            ->put(route('masterapp.time-off-requests.update', $request->id), $payload)
            ->assertStatus(403);
    }

    public function test_update_returns_403_when_non_admin_edits_other_users_request(): void
    {
        $otherUser = User::factory()->create();
        $request = $this->createTimeOffRequest(['user_id' => $otherUser->id]);
        $payload = $this->validStorePayload([
            'user_id' => $otherUser->id,
            'start_time' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'end_time' => now()->addDays(4)->format('Y-m-d H:i:s'),
        ]);
        $this->actingAs($this->userWithListOnly)
            ->from(route('masterapp.time-off-requests.index'))
            ->put(route('masterapp.time-off-requests.update', $request->id), $payload)
            ->assertStatus(403);
    }

    public function test_update_redirects_with_error_when_non_admin_edits_non_pending(): void
    {
        $request = $this->createTimeOffRequest([
            'user_id' => $this->userWithListOnly->id,
            'status' => 'approved_paid',
        ]);
        $payload = $this->validStorePayload([
            'user_id' => $this->userWithListOnly->id,
            'start_time' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'end_time' => now()->addDays(4)->format('Y-m-d H:i:s'),
        ]);
        $response = $this->actingAs($this->userWithListOnly)
            ->from(route('masterapp.time-off-requests.index'))
            ->put(route('masterapp.time-off-requests.update', $request->id), $payload);
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Only pending requests can be modified.');
    }

    public function test_update_succeeds_when_non_admin_edits_own_pending(): void
    {
        Notification::fake();
        $request = $this->createTimeOffRequest(['user_id' => $this->userWithListOnly->id]);
        $newStart = now()->addDays(5)->format('Y-m-d H:i:s');
        $newEnd = now()->addDays(6)->format('Y-m-d H:i:s');
        $payload = $this->validStorePayload([
            'user_id' => $this->userWithListOnly->id,
            'start_time' => $newStart,
            'end_time' => $newEnd,
            'notes' => 'Updated notes',
        ]);
        $response = $this->actingAs($this->userWithListOnly)
            ->from(route('masterapp.time-off-requests.index'))
            ->put(route('masterapp.time-off-requests.update', $request->id), $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Time off request updated successfully.');
        $request->refresh();
        $this->assertSame('Updated notes', $request->notes);
    }

    public function test_update_succeeds_when_admin_updates_any_request(): void
    {
        Notification::fake();
        $request = $this->createTimeOffRequest(['user_id' => $this->userWithListOnly->id, 'status' => 'approved_paid']);
        $payload = $this->validStorePayload([
            'user_id' => $this->userWithListOnly->id,
            'start_time' => now()->addDays(7)->format('Y-m-d H:i:s'),
            'end_time' => now()->addDays(8)->format('Y-m-d H:i:s'),
            'status' => 'denied',
        ]);
        $response = $this->actingAs($this->userAdmin)
            ->from(route('masterapp.time-off-requests.index'))
            ->put(route('masterapp.time-off-requests.update', $request->id), $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('time_off_requests', ['id' => $request->id, 'status' => 'denied']);
    }

    public function test_update_validates_required_fields(): void
    {
        $request = $this->createTimeOffRequest(['user_id' => $this->userWithListOnly->id]);
        $this->actingAs($this->userWithListOnly)
            ->from(route('masterapp.time-off-requests.index'))
            ->put(route('masterapp.time-off-requests.update', $request->id), [])
            ->assertSessionHasErrors(['user_id', 'start_time', 'end_time', 'status']);
    }

    // ---- destroy ----
    public function test_destroy_returns_403_without_delete_permission(): void
    {
        /** @var User $userNoPerm */
        $userNoPerm = User::factory()->create();
        $userNoPerm->givePermissionTo('list-time-off-requests');
        $request = $this->createTimeOffRequest(['user_id' => $this->userWithListOnly->id]);
        $this->actingAs($userNoPerm)
            ->from(route('masterapp.time-off-requests.index'))
            ->delete(route('masterapp.time-off-requests.destroy', $request->id))
            ->assertStatus(403);
    }

    public function test_destroy_returns_403_when_non_admin_deletes_other_users_request(): void
    {
        $otherUser = User::factory()->create();
        $request = $this->createTimeOffRequest(['user_id' => $otherUser->id]);
        $this->actingAs($this->userWithListOnly)
            ->from(route('masterapp.time-off-requests.index'))
            ->delete(route('masterapp.time-off-requests.destroy', $request->id))
            ->assertStatus(403);
    }

    public function test_destroy_redirects_with_error_when_non_admin_deletes_non_pending(): void
    {
        $request = $this->createTimeOffRequest([
            'user_id' => $this->userWithListOnly->id,
            'status' => 'approved_paid',
        ]);
        $response = $this->actingAs($this->userWithListOnly)
            ->from(route('masterapp.time-off-requests.index'))
            ->delete(route('masterapp.time-off-requests.destroy', $request->id));
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Only pending requests can be deleted.');
        $this->assertDatabaseHas('time_off_requests', ['id' => $request->id]);
    }

    public function test_destroy_succeeds_when_non_admin_deletes_own_pending(): void
    {
        Notification::fake();
        $request = $this->createTimeOffRequest(['user_id' => $this->userWithListOnly->id]);
        $response = $this->actingAs($this->userWithListOnly)
            ->from(route('masterapp.time-off-requests.index'))
            ->delete(route('masterapp.time-off-requests.destroy', $request->id));
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Time off request deleted successfully.');
        $this->assertDatabaseMissing('time_off_requests', ['id' => $request->id]);
    }

    public function test_destroy_succeeds_when_admin_deletes_any_request(): void
    {
        Notification::fake();
        $request = $this->createTimeOffRequest(['user_id' => $this->userWithListOnly->id, 'status' => 'approved_paid']);
        $response = $this->actingAs($this->userAdmin)
            ->from(route('masterapp.time-off-requests.index'))
            ->delete(route('masterapp.time-off-requests.destroy', $request->id));
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('time_off_requests', ['id' => $request->id]);
    }

    // ---- updateStatus ----
    public function test_update_status_returns_403_without_status_or_admin_permission(): void
    {
        $request = $this->createTimeOffRequest(['user_id' => $this->userWithListOnly->id]);
        $this->actingAs($this->userWithListOnly)
            ->patch(route('masterapp.time-off-requests.updateStatus', $request->id), ['status' => 'approved_paid'])
            ->assertStatus(403)
            ->assertJson(['success' => false, 'message' => 'Unauthorized']);
    }

    public function test_update_status_succeeds_with_permission(): void
    {
        $request = $this->createTimeOffRequest(['user_id' => $this->userWithListOnly->id]);
        $response = $this->actingAs($this->userAdmin)
            ->patchJson(route('masterapp.time-off-requests.updateStatus', $request->id), ['status' => 'approved_paid']);
        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'message' => 'Status updated successfully']);
        $this->assertDatabaseHas('time_off_requests', ['id' => $request->id, 'status' => 'approved_paid']);
    }

    public function test_update_status_validates_status_enum(): void
    {
        $request = $this->createTimeOffRequest(['user_id' => $this->userWithListOnly->id]);
        $response = $this->actingAs($this->userAdmin)
            ->patchJson(route('masterapp.time-off-requests.updateStatus', $request->id), ['status' => 'invalid']);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);
    }

    public function test_update_status_requires_status(): void
    {
        $request = $this->createTimeOffRequest(['user_id' => $this->userWithListOnly->id]);
        $response = $this->actingAs($this->userAdmin)
            ->patchJson(route('masterapp.time-off-requests.updateStatus', $request->id), []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);
    }

    // ---- bulkUpdateStatus ----
    public function test_bulk_update_status_returns_403_without_permission(): void
    {
        $this->actingAs($this->userWithListOnly)
            ->postJson(route('masterapp.time-off-requests.bulkUpdateStatus'), [
                'status' => 'approved_paid',
                'ids' => [1],
            ])
            ->assertStatus(403)
            ->assertJson(['success' => false, 'message' => 'Unauthorized']);
    }

    public function test_bulk_update_status_succeeds_with_ids(): void
    {
        $r1 = $this->createTimeOffRequest(['user_id' => $this->userWithListOnly->id]);
        $r2 = $this->createTimeOffRequest(['user_id' => $this->userWithListOnly->id]);
        $response = $this->actingAs($this->userAdmin)
            ->postJson(route('masterapp.time-off-requests.bulkUpdateStatus'), [
                'status' => 'approved_unpaid',
                'ids' => [$r1->id, $r2->id],
            ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'message' => 'Statuses updated successfully']);
        $this->assertDatabaseHas('time_off_requests', ['id' => $r1->id, 'status' => 'approved_unpaid']);
        $this->assertDatabaseHas('time_off_requests', ['id' => $r2->id, 'status' => 'approved_unpaid']);
    }

    public function test_bulk_update_status_validates_status_and_ids(): void
    {
        $this->actingAs($this->userAdmin)
            ->postJson(route('masterapp.time-off-requests.bulkUpdateStatus'), [])
            ->assertStatus(422);
        $this->actingAs($this->userAdmin)
            ->postJson(route('masterapp.time-off-requests.bulkUpdateStatus'), [
                'status' => 'invalid',
                'ids' => [1],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    // ---- export ----
    public function test_export_returns_csv_for_admin(): void
    {
        $response = $this->actingAs($this->userAdmin)
            ->get(route('masterapp.time-off-requests.export'), ['type' => 'csv']);
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_export_filters_by_user_when_not_admin(): void
    {
        $response = $this->actingAs($this->userWithListOnly)
            ->get(route('masterapp.time-off-requests.export'));
        $response->assertStatus(200);
    }
}
