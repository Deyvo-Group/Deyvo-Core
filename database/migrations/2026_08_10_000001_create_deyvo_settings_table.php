<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deyvo_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 120)->unique();
            $table->string('label', 160)->nullable();
            $table->string('group', 80)->default('Algemeen')->index();
            $table->string('type', 40)->default('text')->index();
            $table->text('value')->nullable();
            $table->json('options')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deyvo_settings');
    }
};
