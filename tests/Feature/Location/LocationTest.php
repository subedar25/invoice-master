<?php

namespace Tests\Feature\Location;

use App\Models\Location;
use App\Models\User;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LocationTest extends TestCase
{
    use RefreshDatabase;

    protected User $userWithPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRole();
        $this->userWithPermissions = $this->createUserWithLocationPermissions();
    }

    protected function seedPermissionsAndRole(): void
    {
        $permissionNames = [
            'list-locations',
            'create-location',
            'edit-location',
            'delete-location',
        ];
        foreach ($permissionNames as $name) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['slug' => $name, 'display_name' => $name, 'guard_name' => 'web']
            );
        }
        $role = Role::firstOrCreate(['name' => 'location-admin', 'guard_name' => 'web']);
        $role->syncPermissions($permissionNames);
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function createUserWithLocationPermissions(): User
    {
        $user = User::factory()->create();
        $user->assignRole('location-admin');
        return $user;
    }

    protected function validLocationPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test Location',
            'address' => '123 Main St',
            'city' => 'New York',
            'state' => 'NY',
            'country' => 'USA',
            'postal_code' => '10001',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
        ], $overrides);
    }

    public function test_index_requires_list_permission(): void
    {
        /** @var User $userNoPerm */
        $userNoPerm = User::factory()->create();
        $this->actingAs($userNoPerm)
            ->get(route('masterapp.locations.index'))
            ->assertStatus(403);
    }

    public function test_index_returns_200_with_permission(): void
    {
        $this->actingAs($this->userWithPermissions)
            ->get(route('masterapp.locations.index'))
            ->assertStatus(200);
    }

    public function test_index_shows_locations_paginated(): void
    {
        Location::create($this->validLocationPayload(['name' => 'First Location']));
        $response = $this->actingAs($this->userWithPermissions)
            ->get(route('masterapp.locations.index'));
        $response->assertStatus(200);
        $response->assertViewIs('masterapp.locations.index');
        $response->assertViewHas('locations');
    }

    public function test_get_locations_data_returns_json(): void
    {
        Location::create($this->validLocationPayload());
        $response = $this->actingAs($this->userWithPermissions)
            ->get(route('masterapp.locations.data'), [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
            ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
        $data = $response->json();
        $this->assertGreaterThanOrEqual(0, $data['recordsTotal']);
        $this->assertIsArray($data['data']);
    }

    public function test_get_locations_data_accepts_filters(): void
    {
        $response = $this->actingAs($this->userWithPermissions)
            ->get(route('masterapp.locations.data'), [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
                'city' => 'NYC',
                'state' => 'NY',
                'country' => 'USA',
            ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
    }

    public function test_show_returns_200_with_list_permission(): void
    {
        $location = Location::create($this->validLocationPayload());
        $this->actingAs($this->userWithPermissions)
            ->get(route('masterapp.locations.show', $location))
            ->assertStatus(200);
    }

    public function test_show_returns_view_with_location(): void
    {
        $location = Location::create($this->validLocationPayload(['name' => 'Show Me']));
        $response = $this->actingAs($this->userWithPermissions)
            ->get(route('masterapp.locations.show', $location));
        $response->assertStatus(200);
        $response->assertViewIs('masterapp.locations.show');
        $response->assertViewHas('location', $location);
    }

    public function test_destroy_returns_403_without_delete_permission(): void
    {
        /** @var User $userNoDelete */
        $userNoDelete = User::factory()->create();
        $userNoDelete->givePermissionTo('list-locations');
        $location = Location::create($this->validLocationPayload());
        $this->actingAs($userNoDelete)
            ->delete(route('masterapp.locations.destroy', $location->id))
            ->assertStatus(403);
    }

    public function test_destroy_deletes_location_and_returns_success(): void
    {
        Notification::fake();
        $location = Location::create($this->validLocationPayload(['name' => 'To Delete']));
        $response = $this->actingAs($this->userWithPermissions)
            ->delete(route('masterapp.locations.destroy', $location->id));
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Location deleted successfully',
        ]);
        $this->assertSoftDeleted('locations', ['id' => $location->id]);
    }

    public function test_destroy_returns_404_for_nonexistent_location(): void
    {
        $this->actingAs($this->userWithPermissions)
            ->delete(route('masterapp.locations.destroy', 99999))
            ->assertStatus(404);
    }

    public function test_store_creates_location_and_returns_success(): void
    {
        Notification::fake();
        $payload = $this->validLocationPayload(['name' => 'New Location']);
        $response = $this->actingAs($this->userWithPermissions)
            ->postJson(route('masterapp.locations.store'), $payload);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Location entry created successfully.',
        ]);
        $this->assertDatabaseHas('locations', [
            'name' => 'New Location',
            'address' => '123 Main St',
            'city' => 'New York',
            'state' => 'NY',
            'country' => 'USA',
            'postal_code' => '10001',
        ]);
    }

    public function test_store_name_is_required(): void
    {
        $payload = $this->validLocationPayload(['name' => '']);
        $response = $this->actingAs($this->userWithPermissions)
            ->postJson(route('masterapp.locations.store'), $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_store_address_is_required(): void
    {
        $payload = $this->validLocationPayload(['address' => '']);
        $response = $this->actingAs($this->userWithPermissions)
            ->postJson(route('masterapp.locations.store'), $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['address']);
    }

    public function test_store_city_state_country_postal_code_required(): void
    {
        $payload = $this->validLocationPayload();
        unset($payload['city'], $payload['state'], $payload['country'], $payload['postal_code']);
        $response = $this->actingAs($this->userWithPermissions)
            ->postJson(route('masterapp.locations.store'), $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['city', 'state', 'country', 'postal_code']);
    }

    public function test_store_accepts_nullable_latitude_longitude(): void
    {
        Notification::fake();
        $payload = $this->validLocationPayload(['name' => 'No Coords']);
        unset($payload['latitude'], $payload['longitude']);
        $response = $this->actingAs($this->userWithPermissions)
            ->postJson(route('masterapp.locations.store'), $payload);
        $response->assertStatus(200);
        $this->assertDatabaseHas('locations', ['name' => 'No Coords']);
    }

    public function test_store_validates_latitude_bounds(): void
    {
        $payload = $this->validLocationPayload(['latitude' => 100]);
        $response = $this->actingAs($this->userWithPermissions)
            ->postJson(route('masterapp.locations.store'), $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['latitude']);
    }

    public function test_store_validates_longitude_bounds(): void
    {
        $payload = $this->validLocationPayload(['longitude' => 200]);
        $response = $this->actingAs($this->userWithPermissions)
            ->postJson(route('masterapp.locations.store'), $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['longitude']);
    }

    public function test_edit_returns_200_with_edit_permission(): void
    {
        $location = Location::create($this->validLocationPayload());
        $response = $this->actingAs($this->userWithPermissions)
            ->get(route('masterapp.locations.edit', $location));
        $response->assertStatus(200);
        $response->assertViewIs('masterapp.locations.edit');
        $response->assertViewHas('location', $location);
    }

    public function test_update_modifies_location_and_returns_success(): void
    {
        Notification::fake();
        $location = Location::create($this->validLocationPayload(['name' => 'Original']));
        $payload = $this->validLocationPayload([
            'name' => 'Updated Name',
            'address' => '456 New Ave',
            'city' => 'Boston',
            'state' => 'MA',
            'country' => 'USA',
            'postal_code' => '02101',
        ]);
        $response = $this->actingAs($this->userWithPermissions)
            ->putJson(route('masterapp.locations.update', $location), $payload);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Location updated successfully.',
        ]);
        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'name' => 'Updated Name',
            'address' => '456 New Ave',
            'city' => 'Boston',
            'state' => 'MA',
        ]);
    }

    public function test_update_name_is_required(): void
    {
        $location = Location::create($this->validLocationPayload());
        $payload = $this->validLocationPayload(['name' => '']);
        $response = $this->actingAs($this->userWithPermissions)
            ->putJson(route('masterapp.locations.update', $location), $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_update_validates_required_fields(): void
    {
        $location = Location::create($this->validLocationPayload());
        $payload = ['name' => 'Only Name'];
        $response = $this->actingAs($this->userWithPermissions)
            ->putJson(route('masterapp.locations.update', $location), $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['address', 'city', 'state', 'country', 'postal_code']);
    }

    public function test_json_returns_location_data(): void
    {
        $location = Location::create($this->validLocationPayload([
            'name' => 'Json Location',
            'city' => 'Chicago',
            'state' => 'IL',
        ]));
        $response = $this->actingAs($this->userWithPermissions)
            ->get(route('masterapp.locations.json', $location->id));
        $response->assertStatus(200);
        $response->assertJson([
            'id' => $location->id,
            'name' => 'Json Location',
            'address' => $location->address,
            'country' => $location->country,
            'state' => 'IL',
            'city' => 'Chicago',
            'postal_code' => $location->postal_code,
            'latitude' => (float) $location->latitude,
            'longitude' => (float) $location->longitude,
        ]);
    }

    public function test_json_returns_soft_deleted_location(): void
    {
        $location = Location::create($this->validLocationPayload(['name' => 'Deleted One']));
        $location->delete();
        $response = $this->actingAs($this->userWithPermissions)
            ->get(route('masterapp.locations.json', $location->id));
        $response->assertStatus(200);
        $response->assertJson(['id' => $location->id, 'name' => 'Deleted One']);
    }

    public function test_json_returns_404_for_nonexistent_id(): void
    {
        $this->actingAs($this->userWithPermissions)
            ->get(route('masterapp.locations.json', 99999))
            ->assertStatus(404);
    }
}
