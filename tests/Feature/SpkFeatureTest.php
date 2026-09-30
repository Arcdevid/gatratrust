<?php

namespace Tests\Feature;

use App\Models\ProjectTbl;
use App\Models\Role;
use App\Models\Spk;
use App\Models\User;
use Tests\Support\CreatesSpkSchema;
use Tests\TestCase;

class SpkFeatureTest extends TestCase
{
    use CreatesSpkSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSpkSchema();
    }

    private function loginUser(): User
    {
        $role = Role::create(['name' => 'Admin']);
        $user = User::factory()->create(['role_id' => $role->id, 'name' => 'admin-test']);
        $this->actingAs($user);

        return $user;
    }

    public function test_spk_index_route_is_accessible(): void
    {
        $this->loginUser();
        $this->get(route('spk.index'))->assertOk()->assertSee('SPK');
    }

    public function test_spk_export_pdf_endpoint_returns_pdf_response(): void
    {
        $user = $this->loginUser();
        $project = ProjectTbl::factory()->create([
            'client_id' => $user->id, 'created_by' => $user->id,
        ]);

        $this->post(route('spk.store'), [
            'nomor' => 'SPK-001', 'tanggal' => '2026-09-30',
            'project_id' => $project->id, 'data_proyek' => ['running_pqr'],
        ])->assertRedirect(route('spk.index'));

        $spk = Spk::where('nomor', 'SPK-001')->firstOrFail();
        $this->assertSame($project->id, $spk->project_id);
        $this->get(route('spk.exportPdf', $spk))
            ->assertOk()->assertHeader('content-type', 'application/pdf');
    }
}
