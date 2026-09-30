<?php

namespace Tests\Feature;

use App\Models\Kerjaan;
use App\Models\ProjectTbl;
use App\Models\Role;
use App\Models\Spk;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\Support\CreatesSpkSchema;
use Tests\TestCase;

class SpkTest extends TestCase
{
    use CreatesSpkSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createSpkSchema();
    }

    public function test_index_route_returns_200(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->actingAs($user)->get('/spk');

        $response->assertOk();
    }

    public function test_export_pdf_route_returns_pdf_response(): void
    {
        $user = $this->createAuthenticatedUser();

        $project = ProjectTbl::factory()->create([
            'client_id' => $user->id,
            'created_by' => $user->id,
            'kerjaan_id' => Kerjaan::factory()->create()->id,
        ]);

        $spk = Spk::factory()->create([
            'project_id' => $project->id,
        ]);

        $response = $this->actingAs($user)->get(route('spk.exportPdf', $spk));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    private function createAuthenticatedUser(): User
    {
        $role = Role::query()->create(['name' => 'Admin']);

        return User::query()->create([
            'name' => 'user_'.uniqid(),
            'email' => uniqid('user', true).'@example.com',
            'password' => Hash::make('password'),
            'role_id' => $role->id,
        ]);
    }
}
