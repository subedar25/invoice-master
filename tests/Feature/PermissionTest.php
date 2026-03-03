<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Timesheet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // -----------------------------
        // Create permissions
        // -----------------------------
        $permissions = [
            'list-users', 'create-user', 'edit-user', 'delete-user',
            'list-timesheets', 'create-timesheet', 'edit-timesheet', 'delete-timesheet'
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // -----------------------------
        // Create roles
        // -----------------------------
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Assign all permissions to admin
        $adminRole->syncPermissions($permissions);

        // Assign limited permissions to user
        $userRole->syncPermissions(['list-users', 'list-timesheets']);
    }

    // -----------------------------
    // Helper method to create user with role
    // -----------------------------
    protected function createUserWithRole(string $roleName): User
    {
        $user = User::factory()->create([
            'password' => bcrypt('password')
        ]);

        $role = Role::where('name', $roleName)->first();
        $user->assignRole($role);

        return $user;
    }

    // -----------------------------
    // User Permission Tests
    // -----------------------------

    public function test_user_access_permissions_for_users_index()
    {
        $admin = $this->createUserWithRole('admin');
        $user = $this->createUserWithRole('user');
        $guest = User::factory()->create(); // No permissions

        $this->actingAs($admin)->get(route('masterapp.users.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('masterapp.users.index'))->assertStatus(200);
        $this->actingAs($guest)->get(route('masterapp.users.index'))->assertStatus(403);
    }

    public function test_user_access_permissions_for_users_create_and_store()
    {
        $admin = $this->createUserWithRole('admin');
        $user = $this->createUserWithRole('user');

        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        // Access create form
        $this->actingAs($admin)->get(route('masterapp.users.create'))->assertStatus(200);
        $this->actingAs($user)->get(route('masterapp.users.create'))->assertStatus(403);

        // Store user
        $this->actingAs($admin)->post(route('masterapp.users.store'), $userData)->assertRedirect(route('masterapp.users.index'));
        $this->actingAs($user)->post(route('masterapp.users.store'), $userData)->assertStatus(403);
    }

    public function test_user_access_permissions_for_users_edit_and_update()
    {
        $admin = $this->createUserWithRole('admin');
        $user = $this->createUserWithRole('user');
        $targetUser = User::factory()->create();

        $updateData = ['name' => 'Updated Name', 'email' => $targetUser->email];

        // Access edit form
        $this->actingAs($admin)->get(route('masterapp.users.edit', $targetUser))->assertStatus(200);
        $this->actingAs($user)->get(route('masterapp.users.edit', $targetUser))->assertStatus(403);

        // Update user
        $this->actingAs($admin)->put(route('masterapp.users.update', $targetUser), $updateData)->assertRedirect(route('masterapp.users.index'));
        $this->actingAs($user)->put(route('masterapp.users.update', $targetUser), $updateData)->assertStatus(403);
    }

    public function test_user_access_permissions_for_users_delete()
    {
        $admin = $this->createUserWithRole('admin');
        $user = $this->createUserWithRole('user');
        $targetUser = User::factory()->create();

        $this->actingAs($admin)->delete(route('masterapp.users.destroy', $targetUser))->assertStatus(302);
        $this->actingAs($user)->delete(route('masterapp.users.destroy', $targetUser))->assertStatus(403);
    }

    // -----------------------------
    // Timesheet Permission Tests
    // -----------------------------

    public function test_user_access_permissions_for_timesheets_index()
    {
        $admin = $this->createUserWithRole('admin');
        $user = $this->createUserWithRole('user');
        $guest = User::factory()->create();

        $this->actingAs($admin)->get(route('masterapp.timesheets.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('masterapp.timesheets.index'))->assertStatus(200);
        $this->actingAs($guest)->get(route('masterapp.timesheets.index'))->assertStatus(403);
    }

    public function test_user_access_permissions_for_timesheets_store()
    {
        $admin = $this->createUserWithRole('admin');
        $user = $this->createUserWithRole('user');

        $timesheetData = [
            'user_id' => $admin->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '17:00',
        ];

        $this->actingAs($admin)->post(route('masterapp.timesheets.store'), $timesheetData)->assertRedirect();
        $this->actingAs($user)->post(route('masterapp.timesheets.store'), $timesheetData)->assertStatus(403);
    }

    public function test_user_access_permissions_for_timesheets_update()
    {
        $admin = $this->createUserWithRole('admin');
        $user = $this->createUserWithRole('user');
        $timesheet = Timesheet::factory()->create();

        $updateData = ['clock_out' => '18:00'];

        $this->actingAs($admin)->put(route('masterapp.timesheets.update', $timesheet), $updateData)->assertRedirect();
        $this->actingAs($user)->put(route('masterapp.timesheets.update', $timesheet), $updateData)->assertStatus(403);
    }

    public function test_user_access_permissions_for_timesheets_delete()
    {
        $admin = $this->createUserWithRole('admin');
        $user = $this->createUserWithRole('user');
        $timesheet = Timesheet::factory()->create();

        $this->actingAs($admin)->delete(route('masterapp.timesheets.destroy', $timesheet))->assertRedirect();
        $this->actingAs($user)->delete(route('masterapp.timesheets.destroy', $timesheet))->assertStatus(403);
    }
}
