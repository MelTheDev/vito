<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('server_providers', function (Blueprint $table): void {
            $table->unsignedBigInteger('project_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('server_providers', function (Blueprint $table): void {
            $table->dropColumn('project_id');
        });
    }
};
