<?php

namespace Tests\Feature\Masters;

use App\Core\Season\Services\SeasonService;
use App\Models\Season;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SeasonModuleTest extends TestCase
{
    /**
     * Do not use RefreshDatabase: the baseline migration runs MySQL-specific SQL
     * which fails on SQLite. This test only needs the seasons table.
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
        Schema::dropIfExists('seasons');
        Schema::enableForeignKeyConstraints();

        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('code', 255)->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function getService(): SeasonService
    {
        return $this->app->make(SeasonService::class);
    }

    public function test_service_can_be_resolved(): void
    {
        $service = $this->getService();
        $this->assertInstanceOf(SeasonService::class, $service);
    }

    public function test_can_create_season(): void
    {
        $service = $this->getService();

        $record = $service->create([
            'name' => 'Season 2024',
            'description' => 'Main season',
            'status' => true,
        ]);

        $this->assertInstanceOf(Season::class, $record);
        $this->assertDatabaseHas('seasons', [
            'name' => 'Season 2024',
            'description' => 'Main season',
            'status' => true,
        ]);
    }

    public function test_can_create_season_with_optional_description(): void
    {
        $service = $this->getService();

        $record = $service->create([
            'name' => 'Minimal Season',
            'status' => true,
        ]);

        $this->assertDatabaseHas('seasons', [
            'name' => 'Minimal Season',
            'status' => true,
        ]);
        $this->assertNull($record->description);
    }

    public function test_can_update_season(): void
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
        $this->assertDatabaseHas('seasons', [
            'id' => $record->id,
            'name' => 'New Name',
            'description' => 'New desc',
            'status' => false,
        ]);
    }

    public function test_can_delete_season(): void
    {
        $service = $this->getService();

        $record = $service->create([
            'name' => 'Delete Me',
            'status' => true,
        ]);

        $service->delete($record->id);

        $this->assertSoftDeleted('seasons', ['id' => $record->id]);
    }

    public function test_list_returns_paginator(): void
    {
        $service = $this->getService();

        $service->create(['name' => 'Season A', 'status' => true]);
        $service->create(['name' => 'Season B', 'status' => true]);

        $result = $service->list('', '', 'created_at', 'desc', 15, 1);

        $this->assertGreaterThanOrEqual(2, $result->total());
        $this->assertCount(2, $result->items());
    }

    public function test_find_returns_season(): void
    {
        $service = $this->getService();

        $record = $service->create(['name' => 'Find Me', 'status' => true]);

        $found = $service->find($record->id);

        $this->assertInstanceOf(Season::class, $found);
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

    public function test_get_for_view_returns_season(): void
    {
        $service = $this->getService();

        $record = $service->create(['name' => 'View Me', 'description' => 'Desc', 'status' => true]);

        $view = $service->getForView($record->id);

        $this->assertInstanceOf(Season::class, $view);
        $this->assertEquals($record->id, $view->id);
    }
}
