<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->table('tasks', function (Blueprint $table) {
            $table->string('status')->default('todo')->after('title');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->table('tasks', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
