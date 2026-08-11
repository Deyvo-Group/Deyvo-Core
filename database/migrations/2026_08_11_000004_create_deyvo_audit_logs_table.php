<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deyvo_audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('event', 120)->index();
            $table->string('subject_type', 160)->nullable();
            $table->string('subject_id', 120)->nullable();
            $table->string('subject_label', 255)->nullable()->index();
            $table->string('actor_id', 120)->nullable()->index();
            $table->string('actor_name', 160)->nullable()->index();
            $table->string('actor_email', 255)->nullable();
            $table->string('request_id', 64)->nullable()->index();
            $table->string('method', 10)->nullable();
            $table->string('path', 2048)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->json('context')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deyvo_audit_logs');
    }
};
