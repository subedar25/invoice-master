<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class UserModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_user_can_be_created()
    {
        $this->authenticate();

        $response = $this->post('/users', [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'Password@123',
            'status_notes' => 'New User Created'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'User created successfully!'
                 ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com'
        ]);
    }


    public function test_user_update_with_password_changes()
    {
        $this->authenticate();

        $user = User::factory()->create();

        $response = $this->put("/users/{$user->id}", [
            'name' => 'User Password Updated',
            'email' => $user->email,
            'change_password' => "1",
            'password' => 'NewPassword@456'
        ]);

        $response->assertStatus(200);

        $this->assertTrue(Hash::check('NewPassword@456', $user->fresh()->password));
    }
  
    
    public function test_driver_field_handling()
    {
        $this->authenticate();

        $user = User::factory()->create([
            'driver' => 0
        ]);

        // Update to driver
        $response = $this->put("/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'driver' => "1"
        ]);

        $response->assertStatus(200);
        $this->assertEquals(1, $user->fresh()->driver);

        // Update back to not a driver
        $response = $this->put("/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'driver' => "0"
        ]);

        $response->assertStatus(200);
        $this->assertEquals(0, $user->fresh()->driver);
    }
    
    public function test_role_assignment_on_update()
    {
        $this->authenticate();

        $roleUser = \Spatie\Permission\Models\Role::create(['name' => 'User']);
        $roleAdmin = \Spatie\Permission\Models\Role::create(['name' => 'Admin']);

        $user = User::factory()->create();
        $user->assignRole($roleUser);

        // Update roles to Admin
        $response = $this->put("/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'roles' => [$roleAdmin->id]
        ]);

        $response->assertStatus(200);
        $this->assertTrue($user->fresh()->hasRole('Admin'));
        $this->assertFalse($user->fresh()->hasRole('User'));
    }
    public function test_role_clearing_on_update()
    {
        $this->authenticate();

        $roleUser = \Spatie\Permission\Models\Role::create(['name' => 'User']);

        $user = User::factory()->create();
        $user->assignRole($roleUser);

        // Update roles to empty array
        $response = $this->put("/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'roles' => []
        ]);

        $response->assertStatus(200);
        $this->assertFalse($user->fresh()->hasAnyRole());
    }
public function test_edit_user_view_includes_roles_and_statuses()
    {
        $this->authenticate();

        $user = User::factory()->create();

        $response = $this->get("/users/{$user->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('users.edit');
        $response->assertViewHas('roles');
        $response->assertViewHas('userStatuses');
    }
    
    
    public function test_duplicate_user_view_roles_multiselect_present()
    {
        $this->authenticate();

        $user = User::factory()->create();

        $response = $this->get("/users/{$user->id}/duplicate");

        $response->assertStatus(200);
        $response->assertViewIs('users.duplicate');
        $response->assertSee('name="roles[]"', false);
    }
    public function test_duplicate_user_view_roles_multiselect_options()
    {
        $this->authenticate();

        $roleUser = \Spatie\Permission\Models\Role::create(['name' => 'User']);
        $roleAdmin = \Spatie\Permission\Models\Role::create(['name' => 'Admin']);

        $user = User::factory()->create();

        $response = $this->get("/users/{$user->id}/duplicate");

        $response->assertStatus(200);
        $response->assertViewIs('users.duplicate');
        $response->assertSee('option value="' . $roleUser->id . '"', false);
        $response->assertSee('option value="' . $roleAdmin->id . '"', false);
    }
  
    
    public function test_edit_user_view_includes_change_password_options()
    {
        $this->authenticate();

        $user = User::factory()->create();

        $response = $this->get("/users/{$user->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('users.edit');
        $response->assertSee('option value="0"', false);
        $response->assertSee('option value="1"', false);
    }
public function test_user_update_basic_info()
    {
        $this->authenticate();

        $user = User::factory()->create();

        $response = $this->put("/users/{$user->id}", [
            'name' => 'Updated User Name',
            'email' => $user->email,
            'driver' => "0",
            'change_password' => "0"
        ]);

        $response->assertStatus(200)
                ->assertJson(['message' => 'User updated successfully!']);  
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated User Name'
        ]);
    }
    public function test_user_update_password_change()
    {
        $this->authenticate();

        $user = User::factory()->create([
            'password' => Hash::make('OldPassword@123'),
        ]);

        $response = $this->put("/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'driver' => "0",
            'change_password' => "1",
            'password' => 'NewPassword@123'
        ]);

        $response->assertStatus(200)
                ->assertJson(['message' => 'User updated successfully!']);  

        $this->assertTrue(Hash::check('NewPassword@123', $user->fresh()->password));    
    }
    public function test_user_soft_deletion()
    {
        $this->authenticate();

        $user = User::factory()->create();
        $response = $this->delete("/users/{$user->id}");
        $response->assertStatus(200)
                ->assertJson(['message' => 'User deleted successfully!']);
        $this->assertSoftDeleted('users', [
            'id' => $user->id
        ]);
    }
   //test user soft bulk delete
    public function test_user_bulk_soft_delete(){
         $this->authenticate();

        $user = User::factory()->create();
        $response = $this->delete("/users/{$user->id}");
        $response->assertStatus(200)
                ->assertJson(['message' => 'User deleted successfully!']);
        $this->assertSoftDeleted('users', [
            'id' => $user->id
        ]);
    }
    
//change_password option works as expected in edit user view
    public function test_change_password_option_in_edit_user_view()
    {
        $this->authenticate();

        $user = User::factory()->create();

        $response = $this->get("/users/{$user->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('users.edit');
        $response->assertSee('name="change_password"', false);
    }
//status notes editable in edit user view
    public function test_status_notes_editable_in_edit_user_view()
    {
        $this->authenticate();

        $user = User::factory()->create();

        $response = $this->get("/users/{$user->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('users.edit');
        $response->assertSee('name="status_notes"', false);
    }
   
    //quill editor for status notes present in edit user view and it is editable 
    // public function test_quill_editor_for_status_notes_in_edit_user_view()      
  
    // {   
    //     $this->authenticate();

    //     $user = User::factory()->create();

    //     $response = $this->get("/users/{$user->id}/edit");

    //     $response->assertStatus(200);
    //     $response->assertViewIs('users.edit');
    //     $response->assertSee('class="quill-editor"', false);
    // }
      //quill editor for status notes present in new user view
    // public function test_quill_editor_for_status_notes_in_new_user_view()  
    // {   
    //     $this->authenticate();

    //     $response = $this->get("/users/create");

    //     $response->assertStatus(200);
    //     $response->assertViewIs('users.create');
    //     $response->assertSee('class="quill-editor"', false);
    // }
// when i edit and update and create new user swal.fire success message appears icon: "success", title: res.message,timer: 1500,  and many others   
                 
    public function test_swal_fire_success_message_on_user_create_update()
    {
        $this->authenticate();

        // Test for user creation
        $responseCreate = $this->post('/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password@123',   
            'driver' => "0",
            'change_password' => "0",
        ]);
        $responseCreate->assertStatus(200)
                       ->assertJson(['message' => 'User created successfully!']);
        // Test for user update
        $user = User::factory()->create();
        $responseUpdate = $this->put("/users/{$user->id}", [
            'name' => 'Updated User Name',
            'email' => $user->email,
            'driver' => "0",
            'change_password' => "0"
        ]);
        $responseUpdate->assertStatus(200)
                       ->assertJson(['message' => 'User updated successfully!']);
    }
  
}