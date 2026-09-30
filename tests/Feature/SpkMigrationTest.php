<?php

namespace Tests\Feature;

use App\Models\ProjectTbl;
use App\Models\Spk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\CreatesSpkSchema;
use Tests\TestCase;

class SpkMigrationTest extends TestCase
{
    use CreatesSpkSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSpkSchema(false);
    }

    public function test_upgrade_preserves_legacy_records_and_accepts_new_project_spks(): void
    {
        $id = $this->insertLegacySpk();
        $before = (array) DB::table('spks')->find($id);
        $migration = $this->projectMigration();
        $migration->up();

        $this->assertDatabaseHas('spks', $before);
        $project = ProjectTbl::factory()->create();
        $spk = Spk::create([
            'nomor' => 'PROJECT-001', 'tanggal' => '2026-09-30',
            'project_id' => $project->id, 'data_proyek' => ['running_pqr'],
        ]);
        $this->assertSame($project->id, $spk->fresh()->project_id);
        $this->assertSame(['running_pqr'], $spk->fresh()->data_proyek);

        $migration->up(); // A retry after partial MySQL DDL must not duplicate columns.
        $this->assertDatabaseCount('spks', 2);
        $this->assertDatabaseHas('spks', $before);
    }

    public function test_rollback_preserves_the_original_table_and_legacy_record(): void
    {
        $id = $this->insertLegacySpk();
        $before = (array) DB::table('spks')->find($id);
        $migration = $this->projectMigration();
        $migration->up();
        $migration->down();

        $this->assertTrue(Schema::hasTable('spks'));
        $this->assertFalse(Schema::hasColumn('spks', 'project_id'));
        $this->assertFalse(Schema::hasColumn('spks', 'data_proyek'));
        $this->assertDatabaseHas('spks', $before);
        $migration->up();
        $this->assertTrue(Schema::hasColumn('spks', 'project_id'));
    }

    public function test_rollback_refuses_to_discard_project_data(): void
    {
        $migration = $this->projectMigration();
        $migration->up();
        $spk = Spk::factory()->create();

        try {
            $migration->down();
            $this->fail('Rollback should protect project SPKs.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('Rollback SPK dibatalkan', $exception->getMessage());
        }

        $this->assertDatabaseHas('spks', ['id' => $spk->id, 'project_id' => $spk->project_id]);
    }

    public function test_spk_and_multi_client_migrations_run_in_order_on_an_empty_database(): void
    {
        Schema::dropAllTables();
        // Use the real dependency chain; unrelated accounting migrations use MySQL-only SQL.
        $this->artisan('migrate', ['--force' => true, '--path' => [
            'database/migrations/0001_01_01_000000_create_users_table.php',
            'database/migrations/2025_05_13_000936_create_kerjaans_table.php',
            'database/migrations/2025_05_13_002024_create_projects_table.php',
            'database/migrations/2026_02_23_160000_create_spks_table.php',
            'database/migrations/2026_02_25_000001_create_spks_table.php',
            'database/migrations/2026_09_30_000000_create_client_project_table.php',
        ]])->assertSuccessful();
        $this->assertTrue(Schema::hasColumn('spks', 'project_id'));
        $this->assertTrue(Schema::hasTable('client_project'));
    }

    public function test_existing_project_only_schema_is_preserved(): void
    {
        Schema::drop('spks');
        Schema::create('spks', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('nomor');
            $table->date('tanggal');
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->json('data_proyek')->nullable();
            $table->timestamps();
        });
        $spk = Spk::factory()->create();
        $migration = $this->projectMigration();
        $migration->up();
        $migration->down();
        $this->assertDatabaseHas('spks', ['id' => $spk->id, 'project_id' => $spk->project_id]);
    }

    private function projectMigration(): \Illuminate\Database\Migrations\Migration
    {
        return require database_path('migrations/2026_02_25_000001_create_spks_table.php');
    }

    private function insertLegacySpk(): int
    {
        return DB::table('spks')->insertGetId([
            'nomor' => 'LEGACY-001', 'tanggal' => '2026-02-23',
            'pegawai_nama' => 'Budi', 'pegawai_jabatan' => 'Engineer',
            'tujuan_dinas' => 'Audit', 'tanggal_berangkat' => '2026-02-24',
            'tanggal_kembali' => '2026-02-25',
        ]);
    }
}
