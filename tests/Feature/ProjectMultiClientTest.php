<?php

namespace Tests\Feature;

use App\Models\Kerjaan;
use App\Models\ProjectTbl;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProjectMultiClientTest extends TestCase
{
    private User $admin;

    private User $primaryClient;

    private User $secondaryClient;

    private User $unassignedClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame('sqlite', config('database.default'));
        config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);
        $this->createMinimalSchema();

        $adminRole = Role::create(['name' => 'Admin']);
        $clientRole = Role::create(['name' => 'Client']);

        $this->admin = User::factory()->create(['role_id' => $adminRole->id]);
        $this->primaryClient = User::factory()->create([
            'role_id' => $clientRole->id,
            'company' => 'PT Client Utama',
        ]);
        $this->secondaryClient = User::factory()->create([
            'role_id' => $clientRole->id,
            'company' => 'PT Client Kedua',
        ]);
        $this->unassignedClient = User::factory()->create([
            'role_id' => $clientRole->id,
            'company' => 'PT Client Lain',
        ]);
    }

    public function test_admin_can_create_a_project_for_multiple_clients(): void
    {
        $kerjaan = Kerjaan::factory()->create();

        $response = $this->actingAs($this->admin)->post(route('projects.store'), [
            'nama_project' => 'Project Bersama',
            'no_project' => 'PRJ-MULTI-001',
            'client_ids' => [$this->primaryClient->id, $this->secondaryClient->id],
            'kerjaan_id' => $kerjaan->id,
            'start' => now()->startOfYear()->toDateString(),
            'end' => now()->endOfYear()->toDateString(),
            'pic_id' => [$this->admin->id],
        ], ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertOk()->assertJson(['success' => true]);

        $project = ProjectTbl::where('no_project', 'PRJ-MULTI-001')->firstOrFail();

        $this->assertSame($this->primaryClient->id, $project->client_id);
        $this->assertEqualsCanonicalizing(
            [$this->primaryClient->id, $this->secondaryClient->id],
            $project->clients()->pluck('users.id')->all()
        );
    }

    public function test_migration_backfills_the_existing_primary_client(): void
    {
        Schema::drop('client_project');

        $project = ProjectTbl::factory()->create([
            'client_id' => $this->primaryClient->id,
            'created_by' => $this->admin->id,
        ]);

        $migration = require database_path('migrations/2026_09_30_000000_create_client_project_table.php');
        $migration->up();

        $this->assertDatabaseHas('client_project', [
            'project_id' => $project->id,
            'client_id' => $this->primaryClient->id,
        ]);
    }

    public function test_both_assigned_clients_can_see_the_same_project(): void
    {
        $project = $this->createSharedProject();

        $this->assertTrue(
            ProjectTbl::accessibleToClient($this->primaryClient->id)->whereKey($project->id)->exists()
        );
        $this->assertTrue(
            ProjectTbl::accessibleToClient($this->secondaryClient->id)->whereKey($project->id)->exists()
        );

        $this->actingAs($this->primaryClient)
            ->get(route('projects.show', $project))
            ->assertOk();

        $this->actingAs($this->secondaryClient)
            ->get(route('projects.show', $project))
            ->assertOk();
    }

    public function test_unassigned_client_cannot_open_the_project(): void
    {
        $project = $this->createSharedProject();

        $this->actingAs($this->unassignedClient)
            ->get(route('projects.show', $project))
            ->assertForbidden();
    }

    public function test_unassigned_client_is_excluded_from_the_accessible_project_query(): void
    {
        $project = $this->createSharedProject();

        $this->assertFalse(
            ProjectTbl::accessibleToClient($this->unassignedClient->id)->whereKey($project->id)->exists()
        );
    }

    private function createSharedProject(): ProjectTbl
    {
        $project = ProjectTbl::factory()->create([
            'client_id' => $this->primaryClient->id,
            'created_by' => $this->admin->id,
            'start' => now()->startOfYear()->toDateString(),
            'end' => now()->endOfYear()->toDateString(),
        ]);

        $project->clients()->attach([
            $this->primaryClient->id,
            $this->secondaryClient->id,
        ]);

        return $project;
    }

    public function test_update_revokes_removed_client_and_preserves_the_primary_client(): void
    {
        $project = $this->createSharedProject();
        $payload = [
            'nama_project' => $project->nama_project,
            'no_project' => $project->no_project,
            'kerjaan_id' => $project->kerjaan_id,
            'start_project' => $project->start->toDateString(),
            'end_project' => $project->end->toDateString(),
            'client_ids' => [$this->unassignedClient->id, $this->primaryClient->id],
            'pics' => [$this->admin->id],
        ];

        $this->actingAs($this->admin)->postJson(route('projects.update', $project), $payload)
            ->assertRedirect(route('projects.tampilan'));

        $this->assertSame($this->primaryClient->id, $project->fresh()->client_id);
        $this->assertDatabaseMissing('client_project', [
            'project_id' => $project->id, 'client_id' => $this->secondaryClient->id,
        ]);
        $this->actingAs($this->secondaryClient)->get(route('projects.show', $project))->assertForbidden();
        $this->actingAs($this->unassignedClient)->get(route('projects.show', $project))->assertOk();
        $payload['client_ids'] = [$this->unassignedClient->id];
        $this->actingAs($this->admin)->postJson(route('projects.update', $project), $payload)->assertRedirect(route('projects.tampilan'));
        $this->assertSame($this->unassignedClient->id, $project->fresh()->client_id);
    }

    public function test_invalid_client_selections_are_rejected_without_creating_a_project(): void
    {
        $kerjaan = Kerjaan::factory()->create();
        foreach ([[], [$this->admin->id], [$this->primaryClient->id, $this->primaryClient->id], [99999]] as $ids) {
            $this->actingAs($this->admin)->postJson(route('projects.store'), [
                'nama_project' => 'Invalid', 'no_project' => 'INVALID',
                'client_ids' => $ids, 'kerjaan_id' => $kerjaan->id,
                'start' => '2026-01-01', 'end' => '2026-12-31', 'pic_id' => [$this->admin->id],
            ])->assertUnprocessable();
        }
        $this->assertDatabaseCount('projects', 0);
        $this->assertDatabaseCount('client_project', 0);
    }

    public function test_clients_cannot_change_project_membership(): void
    {
        $project = $this->createSharedProject();
        foreach ([$this->primaryClient, $this->secondaryClient, $this->unassignedClient] as $client) {
            $this->actingAs($client)->postJson(route('projects.store'), [])->assertForbidden();
            $this->postJson(route('projects.update', $project), [])->assertForbidden();
            $this->deleteJson(route('projects.destroy', $project))->assertForbidden();
        }
    }

    public function test_ajax_list_shows_a_shared_project_once_for_each_assigned_client(): void
    {
        $project = $this->createSharedProject();
        foreach ([$this->primaryClient, $this->secondaryClient] as $client) {
            $this->actingAs($client)->getJson(route('projects.list'), ['X-Requested-With' => 'XMLHttpRequest'])
                ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $project->id);
        }
        $this->actingAs($this->unassignedClient)->getJson(route('projects.list'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_unassigned_client_cannot_read_project_data_or_post_comments(): void
    {
        $project = $this->createSharedProject();
        $this->actingAs($this->unassignedClient);
        $this->getJson('/project/'.$project->id.'/uploaded-files')->assertForbidden();
        $this->getJson('/get-administrasi-files/'.$project->id)->assertForbidden();
        $payload = ['project_id' => $project->id, 'list_proses_id' => 1, 'urutan_id' => 1, 'comment' => 'Blocked'];
        $this->getJson('/project-detail/comments?'.http_build_query($payload))->assertForbidden();
        $this->postJson(route('project.comments.store'), $payload)->assertForbidden();
    }

    public function test_assigned_clients_get_the_same_non_internal_administration_files(): void
    {
        $project = $this->createSharedProject();
        DB::table('administrasi_files')->insert([
            ['project_id' => $project->id, 'file_name' => 'Shared', 'is_internal' => false],
            ['project_id' => $project->id, 'file_name' => 'Internal', 'is_internal' => true],
        ]);
        foreach ([$this->primaryClient, $this->secondaryClient] as $client) {
            $this->actingAs($client)->getJson('/get-administrasi-files/'.$project->id)
                ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.file_name', 'Shared');
        }
    }

    public function test_migration_can_roll_back_without_removing_the_primary_client(): void
    {
        $project = $this->createSharedProject();
        $migration = require database_path('migrations/2026_09_30_000000_create_client_project_table.php');
        $migration->down();
        $this->assertFalse(Schema::hasTable('client_project'));
        $this->assertSame($this->primaryClient->id, $project->fresh()->client_id);
    }

    private function createMinimalSchema(): void
    {
        Schema::dropAllTables();

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('email');
            $table->string('password');
            $table->foreignId('role_id')->constrained('roles');
            $table->string('company')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('kerjaans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kerjaan')->unique();
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('nama_project');
            $table->string('no_project')->unique();
            $table->foreignId('client_id')->constrained('users');
            $table->unsignedBigInteger('pak_id')->nullable();
            $table->foreignId('kerjaan_id')->constrained('kerjaans');
            $table->text('deskripsi')->nullable();
            $table->decimal('total_biaya_project', 15, 2)->nullable();
            $table->date('start')->nullable();
            $table->date('end')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('client_project', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['project_id', 'client_id']);
        });

        Schema::create('project_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['project_id', 'user_id']);
        });

        Schema::create('list_proses', function (Blueprint $table) {
            $table->id();
            $table->string('nama_proses');
            $table->timestamps();
        });

        Schema::create('kerjaan_list_proses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kerjaan_id');
            $table->unsignedBigInteger('list_proses_id');
            $table->integer('urutan')->default(1);
            $table->integer('hari')->default(1);
            $table->timestamps();
        });

        Schema::create('project_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('kerjaan_list_proses_id');
            $table->integer('urutan_id')->default(1);
            $table->string('status')->default('pending');
            $table->timestamp('start_plan')->nullable();
            $table->timestamp('end_plan')->nullable();
            $table->timestamp('start_action')->nullable();
            $table->timestamp('end_action')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('reference', 100)->nullable();
            $table->text('description');
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
        });

        Schema::create('administrasi_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('file_name');
            $table->boolean('is_internal');
            $table->timestamp('uploaded_at')->nullable();
        });
    }
}
