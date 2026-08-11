<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deyvo_page_revisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('page_id')->constrained('deyvo_pages')->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->string('title', 160);
            $table->string('slug', 160);
            $table->string('template', 120);
            $table->json('sections');
            $table->json('seo');
            $table->timestamps();

            $table->unique(['page_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deyvo_page_revisions');
    }
};
