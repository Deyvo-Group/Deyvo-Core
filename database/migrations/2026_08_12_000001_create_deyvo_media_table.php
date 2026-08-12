<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deyvo_media', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('folder_id')->nullable()->index();
            $table->string('name', 160);
            $table->string('disk', 80)->default('public');
            $table->string('path', 500)->nullable()->index();
            $table->string('url', 2048)->nullable();
            $table->string('mime_type', 160)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('alt', 255)->nullable();
            $table->text('caption')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deyvo_media');
    }
};
