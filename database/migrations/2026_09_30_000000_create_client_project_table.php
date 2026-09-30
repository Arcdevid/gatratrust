<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_project', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['project_id', 'client_id']);
        });

        DB::table('projects')
            ->select(['id', 'client_id'])
            ->whereNotNull('client_id')
            ->orderBy('id')
            ->chunkById(500, function ($projects): void {
                $now = now();
                $rows = $projects->map(fn ($project) => [
                    'project_id' => $project->id,
                    'client_id' => $project->client_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();

                DB::table('client_project')->insertOrIgnore($rows);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_project');
    }
};
