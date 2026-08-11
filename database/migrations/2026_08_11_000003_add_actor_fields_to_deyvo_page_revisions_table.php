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
            $table->string('created_by_id', 120)->nullable()->after('seo');
            $table->string('created_by_name', 160)->nullable()->after('created_by_id');
            $table->string('created_by_email', 255)->nullable()->after('created_by_name');
            $table->string('updated_by_id', 120)->nullable()->after('created_by_email');
            $table->string('updated_by_name', 160)->nullable()->after('updated_by_id');
            $table->string('updated_by_email', 255)->nullable()->after('updated_by_name');
        });
    }

    public function down(): void
    {
        Schema::table('deyvo_page_revisions', function (Blueprint $table): void {
            $table->dropColumn([
                'created_by_id',
                'created_by_name',
                'created_by_email',
                'updated_by_id',
                'updated_by_name',
                'updated_by_email',
            ]);
        });
    }
};
