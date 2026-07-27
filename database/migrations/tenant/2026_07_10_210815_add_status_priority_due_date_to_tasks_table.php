<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->table('tasks', function (Blueprint $table) {
            if (! Schema::connection('tenant')->hasColumn('tasks', 'status')) {
                $table->string('status')->default('todo')->after('description');
            }

            if (! Schema::connection('tenant')->hasColumn('tasks', 'priority')) {
                $table->string('priority')->nullable()->after('status');
            }

            if (! Schema::connection('tenant')->hasColumn('tasks', 'due_date')) {
                $table->timestamp('due_date')->nullable()->after('priority');
            }
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->table('tasks', function (Blueprint $table) {
            if (Schema::connection('tenant')->hasColumn('tasks', 'due_date')) {
                $table->dropColumn('due_date');
            }

            if (Schema::connection('tenant')->hasColumn('tasks', 'priority')) {
                $table->dropColumn('priority');
            }

            if (Schema::connection('tenant')->hasColumn('tasks', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
