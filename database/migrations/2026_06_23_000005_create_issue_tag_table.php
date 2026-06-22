<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Singular-alphabetical naming (issue_tag) so belongsToMany() resolves with zero extra arguments.
        Schema::create('issue_tag', function (Blueprint $table) {
            $table->foreignId('issue_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['issue_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_tag');
    }
};
