<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('deyvo_settings')) {
            return;
        }

        Schema::table('deyvo_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('deyvo_settings', 'label')) {
                $table->string('label', 160)->nullable()->after('key');
            }

            if (! Schema::hasColumn('deyvo_settings', 'group')) {
                $table->string('group', 80)->default('Algemeen')->index()->after('label');
            }

            if (! Schema::hasColumn('deyvo_settings', 'type')) {
                $table->string('type', 40)->default('text')->index()->after('group');
            }

            if (! Schema::hasColumn('deyvo_settings', 'options')) {
                $table->json('options')->nullable()->after('value');
            }
        });
    }

    public function down(): void
    {
        // This migration is intentionally additive for existing package installs.
        // Fresh installs already get these columns from the create migration, so
        // rolling this migration back must not remove columns owned there.
    }
};
