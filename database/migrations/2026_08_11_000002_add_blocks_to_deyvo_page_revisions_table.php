<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deyvo_page_revisions', function (Blueprint $table): void {
            $table->json('blocks')->nullable()->after('sections');
        });
    }

    public function down(): void
    {
        Schema::table('deyvo_page_revisions', function (Blueprint $table): void {
            $table->dropColumn('blocks');
        });
    }
};
