<?php

namespace Tests\Feature\Masters;

use App\Http\Livewire\MasterApp\Masters\Publication as PublicationComponent;
use App\Models\Permission;
use App\Models\Publication;
use App\Models\PublicationType;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PublicationModuleTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createPermissionsAndRole();
        $this->user = User::factory()->create(['active' => true]);
        $this->user->givePermissionTo('list-master', 'publication');
    }

    private function createPermissionsAndRole(): void
    {
        $permissions = [
            ['name' => 'list-master', 'slug' => 'list-master'],
            ['name' => 'publication', 'slug' => 'publication'],
        ];
        foreach ($permissions as $p) {
            Permission::firstOrCreate(
                ['name' => $p['name'], 'guard_name' => 'web'],
                [
                    'name'        => $p['name'],
                    'slug'        => $p['slug'],
                    'display_name' => $p['name'],
                    'guard_name'  => 'web',
                ]
            );
        }
        $role = Role::firstOrCreate(
            ['name' => 'master-editor', 'guard_name' => 'web'],
            ['name' => 'master-editor', 'guard_name' => 'web']
        );
        $role->syncPermissions(['list-master', 'publication']);
    }

    public function test_masters_page_loads_for_authorized_user(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('masterapp.masters'));

        $response->assertStatus(200);
        $response->assertViewIs('masterapp.masters');
    }

    public function test_publication_component_renders(): void
    {
        Livewire::actingAs($this->user)
            ->test(PublicationComponent::class)
            ->assertSee('Publications')
            ->assertSee('Add Publication');
    }

    public function test_publication_open_create_modal(): void
    {
        Livewire::actingAs($this->user)
            ->test(PublicationComponent::class)
            ->call('openCreateModal')
            ->assertSet('showCreateModal', true)
            ->assertSet('showEditModal', false)
            ->assertSet('name', '');
    }

    public function test_publication_create_validation_requires_name_and_publication_type(): void
    {
        Livewire::actingAs($this->user)
            ->test(PublicationComponent::class)
            ->call('openCreateModal')
            ->set('name', '')
            ->set('publication_type_id', '')
            ->call('saveCreate')
            ->assertHasErrors(['name', 'publication_type_id']);
    }

    public function test_publication_can_create_successfully(): void
    {
        $type = PublicationType::create(['publication_type' => 'Issue', 'parent_id' => 0]);

        Livewire::actingAs($this->user)
            ->test(PublicationComponent::class)
            ->call('openCreateModal')
            ->set('name', 'Test Publication')
            ->set('publication_type_id', (string) $type->id)
            ->set('description', 'Test description')
            ->set('status', true)
            ->call('saveCreate')
            ->assertDispatched('formResult');

        $this->assertDatabaseHas('publications', [
            'name' => 'Test Publication',
            'description' => 'Test description',
        ]);
        $pub = Publication::where('name', 'Test Publication')->first();
        $this->assertNotNull($pub);
        $this->assertSame(1, (int) $pub->status);
    }

    public function test_publication_open_edit_modal_and_update(): void
    {
        $type = PublicationType::create(['publication_type' => 'Issue', 'parent_id' => 0]);
        $pub = Publication::create([
            'publication_type_id' => $type->id,
            'name' => 'Original Name',
            'description' => 'Original desc',
            'status' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(PublicationComponent::class)
            ->call('openEditModal', $pub->id)
            ->assertSet('editId', $pub->id)
            ->assertSet('name', 'Original Name')
            ->set('name', 'Updated Name')
            ->set('description', 'Updated desc')
            ->set('publication_type_id', (string) $type->id)
            ->call('saveEdit')
            ->assertDispatched('formResult');
    }

    public function test_publication_open_view_modal(): void
    {
        $type = PublicationType::create(['publication_type' => 'Issue', 'parent_id' => 0]);
        $pub = Publication::create([
            'publication_type_id' => $type->id,
            'name' => 'View Me',
            'status' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(PublicationComponent::class)
            ->call('openViewModal', $pub->id)
            ->assertSet('viewId', $pub->id)
            ->assertSet('showViewModal', true);
    }

    public function test_publication_toggle_status(): void
    {
        $type = PublicationType::create(['publication_type' => 'Issue', 'parent_id' => 0]);
        $pub = Publication::create([
            'publication_type_id' => $type->id,
            'name' => 'Toggle Me',
            'status' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(PublicationComponent::class)
            ->call('toggleStatus', $pub->id)
            ->assertDispatched('statusUpdated');

        $pub->refresh();
        $this->assertFalse($pub->status);
    }

    public function test_publication_delete_by_id(): void
    {
        $type = PublicationType::create(['publication_type' => 'Issue', 'parent_id' => 0]);
        $pub = Publication::create([
            'publication_type_id' => $type->id,
            'name' => 'Delete Me',
            'status' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(PublicationComponent::class)
            ->call('deleteById', $pub->id)
            ->assertDispatched('deleteResult');

        $this->assertSoftDeleted('publications', ['id' => $pub->id]);
    }

    public function test_publication_close_modals(): void
    {
        Livewire::actingAs($this->user)
            ->test(PublicationComponent::class)
            ->call('openCreateModal')
            ->assertSet('showCreateModal', true)
            ->call('closeModals')
            ->assertSet('showCreateModal', false)
            ->assertSet('showEditModal', false)
            ->assertSet('showViewModal', false)
            ->assertSet('editId', null)
            ->assertSet('viewId', null);
    }
}
