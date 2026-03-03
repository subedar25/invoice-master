<?php

namespace Tests\Feature\Masters;

use App\Core\OrganizationType\Services\OrganizationTypeService;
use App\Models\OrganizationType;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrganizationTypeModuleTest extends TestCase
{
    /**
     * Do not use RefreshDatabase: we create only the client_types table
     * to avoid migration/SQLite compatibility issues.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('audit.enabled', false);

        $this->ensureTestSchema();
    }

    private function ensureTestSchema(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('client_types');
        Schema::enableForeignKeyConstraints();

        Schema::create('client_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->boolean('active')->default(true);
            $table->string('code', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function getService(): OrganizationTypeService
    {
        return $this->app->make(OrganizationTypeService::class);
    }

    public function test_service_can_be_resolved(): void
    {
        $service = $this->getService();
        $this->assertInstanceOf(OrganizationTypeService::class, $service);
    }

    public function test_can_create_organization_type(): void
    {
        $service = $this->getService();

        $record = $service->create([
            'name' => 'Retail',
            'description' => 'Retail clients',
            'status' => true,
            'code' => 'retail',
        ]);

        $this->assertInstanceOf(OrganizationType::class, $record);
        $this->assertDatabaseHas('client_types', [
            'name' => 'Retail',
            'description' => 'Retail clients',
            'active' => true,
            'code' => 'retail',
        ]);
        $this->assertNull($record->parent_id);
    }

    public function test_can_create_organization_type_with_optional_description_and_code(): void
    {
        $service = $this->getService();

        $record = $service->create([
            'name' => 'Minimal Type',
            'status' => true,
        ]);

        $this->assertDatabaseHas('client_types', [
            'name' => 'Minimal Type',
            'active' => true,
        ]);
        $this->assertNull($record->description);
        $this->assertNull($record->code);
    }

    public function test_can_create_organization_type_with_parent_id(): void
    {
        $service = $this->getService();

        $parent = $service->create([
            'name' => 'Parent Type',
            'status' => true,
            'code' => 'parent',
        ]);

        $child = $service->create([
            'name' => 'Child Type',
            'description' => 'Child of parent',
            'status' => true,
            'parent_id' => $parent->id,
            'code' => 'child',
        ]);

        $this->assertEquals($parent->id, $child->parent_id);
        $this->assertDatabaseHas('client_types', [
            'id' => $child->id,
            'parent_id' => $parent->id,
        ]);
    }

    public function test_create_ensures_unique_code_when_provided(): void
    {
        $service = $this->getService();

        $first = $service->create([
            'name' => 'First',
            'status' => true,
            'code' => 'duplicate_code',
        ]);
        $this->assertEquals('duplicate_code', $first->code);

        $second = $service->create([
            'name' => 'Second',
            'status' => true,
            'code' => 'duplicate_code',
        ]);
        $this->assertEquals('duplicate_code_2', $second->code);

        $third = $service->create([
            'name' => 'Third',
            'status' => true,
            'code' => 'duplicate_code',
        ]);
        $this->assertEquals('duplicate_code_3', $third->code);
    }

    public function test_can_update_organization_type(): void
    {
        $service = $this->getService();

        $record = $service->create([
            'name' => 'Old Name',
            'description' => 'Old desc',
            'status' => true,
            'code' => 'old_code',
        ]);

        $updated = $service->update($record->id, [
            'name' => 'New Name',
            'description' => 'New desc',
            'status' => false,
        ]);

        $this->assertEquals('New Name', $updated->name);
        $this->assertDatabaseHas('client_types', [
            'id' => $record->id,
            'name' => 'New Name',
            'description' => 'New desc',
            'active' => false,
            'code' => 'old_code',
        ]);
    }

    public function test_can_update_parent_id(): void
    {
        $service = $this->getService();

        $parent = $service->create(['name' => 'Parent', 'status' => true, 'code' => 'p']);
        $child = $service->create(['name' => 'Child', 'status' => true, 'code' => 'c']);
        $this->assertNull($child->parent_id);

        $updated = $service->update($child->id, [
            'name' => 'Child',
            'parent_id' => $parent->id,
        ]);

        $this->assertEquals($parent->id, $updated->parent_id);
        $this->assertDatabaseHas('client_types', [
            'id' => $child->id,
            'parent_id' => $parent->id,
        ]);
    }

    public function test_can_delete_organization_type_soft_deletes(): void
    {
        $service = $this->getService();

        $record = $service->create([
            'name' => 'Delete Me',
            'status' => true,
            'code' => 'del',
        ]);

        $service->delete($record->id);

        $this->assertSoftDeleted('client_types', ['id' => $record->id]);
    }

    public function test_list_returns_paginator(): void
    {
        $service = $this->getService();

        $service->create(['name' => 'Type A', 'status' => true, 'code' => 'a']);
        $service->create(['name' => 'Type B', 'status' => true, 'code' => 'b']);

        $result = $service->list('', '', 'created_at', 'desc', 15, 1);

        $this->assertGreaterThanOrEqual(2, $result->total());
        $this->assertCount(2, $result->items());
    }

    public function test_list_filters_by_search(): void
    {
        $service = $this->getService();

        $service->create(['name' => 'Retail Type', 'status' => true, 'code' => 'retail']);
        $service->create(['name' => 'Lodging Type', 'status' => true, 'code' => 'lodging']);
        $service->create(['name' => 'Retail Plus', 'status' => true, 'code' => 'retail_plus']);

        $result = $service->list('Retail', '', 'created_at', 'desc', 15, 1);

        $this->assertGreaterThanOrEqual(2, $result->total());
        foreach ($result->items() as $item) {
            $this->assertStringContainsStringIgnoringCase('retail', $item->name);
        }
    }

    public function test_list_filters_by_status(): void
    {
        $service = $this->getService();

        $service->create(['name' => 'Active Type', 'status' => true, 'code' => 'act']);
        $service->create(['name' => 'Inactive Type', 'status' => false, 'code' => 'inact']);

        $activeList = $service->list('', '1', 'created_at', 'desc', 15, 1);
        $this->assertGreaterThanOrEqual(1, $activeList->total());
        foreach ($activeList->items() as $item) {
            $this->assertTrue($item->active);
        }

        $inactiveList = $service->list('', '0', 'created_at', 'desc', 15, 1);
        $this->assertGreaterThanOrEqual(1, $inactiveList->total());
        foreach ($inactiveList->items() as $item) {
            $this->assertFalse($item->active);
        }
    }

    public function test_list_loads_parent_relation(): void
    {
        $service = $this->getService();

        $parent = $service->create(['name' => 'Parent', 'status' => true, 'code' => 'parent']);
        $child = $service->create([
            'name' => 'Child',
            'status' => true,
            'code' => 'child',
            'parent_id' => $parent->id,
        ]);

        $result = $service->list('', '', 'created_at', 'desc', 15, 1);

        $childItem = collect($result->items())->firstWhere('id', $child->id);
        $this->assertNotNull($childItem);
        $this->assertTrue($childItem->relationLoaded('parent'));
        $this->assertEquals($parent->id, $childItem->parent->id);
        $this->assertEquals('Parent', $childItem->parent->name);
    }

    public function test_find_returns_organization_type(): void
    {
        $service = $this->getService();

        $record = $service->create(['name' => 'Find Me', 'status' => true, 'code' => 'find']);

        $found = $service->find($record->id);

        $this->assertInstanceOf(OrganizationType::class, $found);
        $this->assertEquals($record->id, $found->id);
        $this->assertEquals('Find Me', $found->name);
    }

    public function test_find_with_trashed_returns_soft_deleted(): void
    {
        $service = $this->getService();

        $record = $service->create(['name' => 'Trashed', 'status' => true, 'code' => 'trash']);
        $service->delete($record->id);

        $found = $service->findWithTrashed($record->id);

        $this->assertNotNull($found);
        $this->assertTrue($found->trashed());
    }

    public function test_get_for_view_returns_with_parent_and_children(): void
    {
        $service = $this->getService();

        $parent = $service->create(['name' => 'Parent View', 'status' => true, 'code' => 'pv']);
        $child1 = $service->create([
            'name' => 'Child One',
            'status' => true,
            'code' => 'c1',
            'parent_id' => $parent->id,
        ]);
        $child2 = $service->create([
            'name' => 'Child Two',
            'status' => true,
            'code' => 'c2',
            'parent_id' => $parent->id,
        ]);

        $view = $service->getForView($parent->id);

        $this->assertInstanceOf(OrganizationType::class, $view);
        $this->assertEquals($parent->id, $view->id);
        $this->assertNull($view->parent_id);
        $this->assertTrue($view->relationLoaded('parent'));
        $this->assertTrue($view->relationLoaded('children'));
        $this->assertCount(2, $view->children);
        $childIds = $view->children->pluck('id')->toArray();
        $this->assertContains($child1->id, $childIds);
        $this->assertContains($child2->id, $childIds);
    }

    public function test_get_for_view_returns_child_with_parent_loaded(): void
    {
        $service = $this->getService();

        $parent = $service->create(['name' => 'My Parent', 'status' => true, 'code' => 'mp']);
        $child = $service->create([
            'name' => 'My Child',
            'status' => true,
            'code' => 'mc',
            'parent_id' => $parent->id,
        ]);

        $view = $service->getForView($child->id);

        $this->assertNotNull($view->parent);
        $this->assertEquals($parent->id, $view->parent->id);
        $this->assertEquals('My Parent', $view->parent->name);
    }

    public function test_get_parent_options_returns_active_only(): void
    {
        $service = $this->getService();

        $service->create(['name' => 'Active Option', 'status' => true, 'code' => 'ao']);
        $inactive = $service->create(['name' => 'Inactive Option', 'status' => false, 'code' => 'io']);

        $options = $service->getParentOptions(null);

        $this->assertGreaterThanOrEqual(1, $options->count());
        foreach ($options as $opt) {
            $this->assertTrue($opt->active);
        }
        $inactiveInOptions = $options->firstWhere('id', $inactive->id);
        $this->assertNull($inactiveInOptions);
    }

    public function test_get_parent_options_excludes_id_when_specified(): void
    {
        $service = $this->getService();

        $first = $service->create(['name' => 'First', 'status' => true, 'code' => 'f1']);
        $second = $service->create(['name' => 'Second', 'status' => true, 'code' => 'f2']);

        $options = $service->getParentOptions($first->id);

        $excluded = $options->firstWhere('id', $first->id);
        $this->assertNull($excluded);
        $included = $options->firstWhere('id', $second->id);
        $this->assertNotNull($included);
    }

    public function test_get_parent_options_ordered_by_name(): void
    {
        $service = $this->getService();

        $service->create(['name' => 'Zebra', 'status' => true, 'code' => 'z']);
        $service->create(['name' => 'Alpha', 'status' => true, 'code' => 'a']);
        $service->create(['name' => 'Middle', 'status' => true, 'code' => 'm']);

        $options = $service->getParentOptions(null);

        $names = $options->pluck('name')->toArray();
        $this->assertEquals(['Alpha', 'Middle', 'Zebra'], $names);
    }

    public function test_model_is_in_use_returns_true_when_has_children(): void
    {
        $service = $this->getService();

        $parent = $service->create(['name' => 'Parent', 'status' => true, 'code' => 'parent']);
        $service->create([
            'name' => 'Child',
            'status' => true,
            'code' => 'child',
            'parent_id' => $parent->id,
        ]);

        $parent->refresh();
        $this->assertTrue($parent->isInUse());
    }

    public function test_model_is_in_use_returns_false_when_no_children(): void
    {
        $service = $this->getService();

        $record = $service->create(['name' => 'Standalone', 'status' => true, 'code' => 'stand']);

        $this->assertFalse($record->isInUse());
    }

    public function test_find_throws_for_soft_deleted(): void
    {
        $service = $this->getService();

        $record = $service->create(['name' => 'Will Delete', 'status' => true, 'code' => 'wd']);
        $service->delete($record->id);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $service->find($record->id);
    }

    public function test_delete_throws_for_missing_id(): void
    {
        $service = $this->getService();

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $service->delete(99999);
    }

    public function test_get_for_view_returns_null_for_missing_id(): void
    {
        $service = $this->getService();

        $view = $service->getForView(99999);

        $this->assertNull($view);
    }
}
