<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deyvo_pages', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 120)->unique();
            $table->string('published_slug', 160)->nullable()->unique();
            $table->unsignedBigInteger('published_revision_id')->nullable()->index();
            $table->unsignedBigInteger('draft_revision_id')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deyvo_pages');
    }
};
