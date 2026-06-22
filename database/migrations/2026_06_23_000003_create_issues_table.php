<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issues', function (Blueprint $table) {
            $table->id();
            // cascadeOnDelete: deleting a project removes its issues (tracker semantic — documented decision)
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            // string columns cast to backed enums — avoids painful native DB enum migrations
            $table->string('status')->default('open');
            $table->string('priority')->default('medium');
            $table->date('due_date')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('priority');
            $table->index('due_date');
            // composite index: serves both the project filter and the status filter in one
            $table->index(['project_id', 'status']);
        });

        // FULLTEXT index for whereFullText() search (MySQL/MariaDB only).
        // scopeSearch() falls back to LIKE on SQLite (used in tests).
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE issues ADD FULLTEXT fulltext_search (title, description)');
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE issues DROP INDEX fulltext_search');
        }

        Schema::dropIfExists('issues');
    }
};
