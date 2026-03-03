<?php

namespace Tests\Feature\Masters;

use App\Core\Advertiser\Services\AdvertiserService;
use App\Models\Advertiser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class AdvertiserModuleTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Uses existing DB schema (baseline + migrations). Each test runs in a
     * transaction and rolls back, so no persistent changes. No RefreshDatabase.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('audit.enabled', false);
    }

    private function getService(): AdvertiserService
    {
        return $this->app->make(AdvertiserService::class);
    }

    public function test_service_can_be_resolved(): void
    {
        $service = $this->getService();
        $this->assertInstanceOf(AdvertiserService::class, $service);
    }

    public function test_can_create_advertiser(): void
    {
        $service = $this->getService();

        $record = $service->create([
            'name' => 'Acme Corp',
            'description' => 'Main advertiser',
            'status' => true,
        ]);

        $this->assertInstanceOf(Advertiser::class, $record);
        $this->assertDatabaseHas('advertisers', [
            'name' => 'Acme Corp',
            'description' => 'Main advertiser',
            'active' => true,
        ]);
        $this->assertNotNull($record->code);
        $this->assertEquals('acme_corp', $record->code);
    }

    public function test_can_create_advertiser_with_optional_description(): void
    {
        $service = $this->getService();

        $record = $service->create([
            'name' => 'Minimal Advertiser',
            'status' => true,
        ]);

        $this->assertDatabaseHas('advertisers', [
            'name' => 'Minimal Advertiser',
            'active' => true,
        ]);
        $this->assertNull($record->description);
        $this->assertEquals('minimal_advertiser', $record->code);
    }

    public function test_create_generates_code_from_name(): void
    {
        $service = $this->getService();

        $record = $service->create([
            'name' => 'Test Advertiser Name',
            'status' => true,
        ]);

        $this->assertEquals('test_advertiser_name', $record->code);
    }

    public function test_create_ensures_unique_code_when_duplicate_exists(): void
    {
        $service = $this->getService();

        $first = $service->create([
            'name' => 'Duplicate',
            'status' => true,
        ]);
        $this->assertEquals('duplicate', $first->code);

        $second = $service->create([
            'name' => 'Duplicate',
            'status' => true,
        ]);
        $this->assertEquals('duplicate_2', $second->code);

        $third = $service->create([
            'name' => 'Duplicate',
            'status' => true,
        ]);
        $this->assertEquals('duplicate_3', $third->code);
    }

    public function test_can_update_advertiser(): void
    {
        $service = $this->getService();

        $record = $service->create([
            'name' => 'Old Name',
            'description' => 'Old desc',
            'status' => true,
        ]);

        $updated = $service->update($record->id, [
            'name' => 'New Name',
            'description' => 'New desc',
            'status' => false,
        ]);

        $this->assertEquals('New Name', $updated->name);
        $this->assertDatabaseHas('advertisers', [
            'id' => $record->id,
            'name' => 'New Name',
            'description' => 'New desc',
            'active' => false,
        ]);
    }

    public function test_can_delete_advertiser_soft_deletes(): void
    {
        $service = $this->getService();

        $record = $service->create([
            'name' => 'Delete Me',
            'status' => true,
        ]);

        $service->delete($record->id);

        $this->assertSoftDeleted('advertisers', ['id' => $record->id]);
    }

    public function test_list_returns_paginator(): void
    {
        $service = $this->getService();

        $a = $service->create(['name' => 'Advertiser A', 'status' => true]);
        $b = $service->create(['name' => 'Advertiser B', 'status' => true]);

        $result = $service->list('', '', 'created_at', 'desc', 15, 1);

        $this->assertGreaterThanOrEqual(2, $result->total());
        $names = array_map(fn ($item) => $item->name, $result->items());
        $this->assertContains('Advertiser A', $names);
        $this->assertContains('Advertiser B', $names);
    }

    public function test_list_filters_by_search(): void
    {
        $service = $this->getService();

        $service->create(['name' => 'Alpha Advertiser', 'status' => true]);
        $service->create(['name' => 'Beta Brand', 'status' => true]);
        $service->create(['name' => 'Alpha Second', 'status' => true]);

        $result = $service->list('Alpha', '', 'created_at', 'desc', 15, 1);

        $this->assertGreaterThanOrEqual(2, $result->total());
        foreach ($result->items() as $item) {
            $this->assertStringContainsStringIgnoringCase('alpha', $item->name);
        }
    }

    public function test_list_filters_by_status(): void
    {
        $service = $this->getService();

        $service->create(['name' => 'Active One', 'status' => true]);
        $service->create(['name' => 'Inactive One', 'status' => false]);

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

    public function test_find_returns_advertiser(): void
    {
        $service = $this->getService();

        $record = $service->create(['name' => 'Find Me', 'status' => true]);

        $found = $service->find($record->id);

        $this->assertInstanceOf(Advertiser::class, $found);
        $this->assertEquals($record->id, $found->id);
        $this->assertEquals('Find Me', $found->name);
    }

    public function test_find_with_trashed_returns_soft_deleted(): void
    {
        $service = $this->getService();

        $record = $service->create(['name' => 'Trashed', 'status' => true]);
        $service->delete($record->id);

        $found = $service->findWithTrashed($record->id);

        $this->assertNotNull($found);
        $this->assertTrue($found->trashed());
    }

    public function test_get_for_view_returns_advertiser(): void
    {
        $service = $this->getService();

        $record = $service->create([
            'name' => 'View Me',
            'description' => 'Desc',
            'status' => true,
        ]);

        $view = $service->getForView($record->id);

        $this->assertInstanceOf(Advertiser::class, $view);
        $this->assertEquals($record->id, $view->id);
    }

    public function test_get_for_view_returns_soft_deleted_advertiser(): void
    {
        $service = $this->getService();

        $record = $service->create(['name' => 'Deleted View', 'status' => true]);
        $service->delete($record->id);

        $view = $service->getForView($record->id);

        $this->assertNotNull($view);
        $this->assertEquals($record->id, $view->id);
        $this->assertTrue($view->trashed());
    }

    public function test_model_is_in_use_returns_false_when_no_relations(): void
    {
        $service = $this->getService();

        $record = $service->create(['name' => 'Standalone', 'status' => true]);

        $this->assertFalse($record->isInUse());
    }

    public function test_find_throws_for_soft_deleted(): void
    {
        $service = $this->getService();

        $record = $service->create(['name' => 'Will Delete', 'status' => true]);
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
}
