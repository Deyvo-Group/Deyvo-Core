<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deyvo_contents', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 120)->unique();
            $table->string('title', 160);
            $table->longText('body')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deyvo_contents');
    }
};
