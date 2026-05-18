<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Reemplazamos la columna enum por una string (más portable entre MySQL/SQLite)
        // y migramos los valores existentes 'user' -> 'customer'.
        Schema::table('users', function (Blueprint $table) {
            $table->string('role_new', 32)->default('customer')->after('password');
        });

        DB::table('users')->update([
            'role_new' => DB::raw("CASE WHEN role = 'admin' THEN 'admin' ELSE 'customer' END"),
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('role_new', 'role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role_old', 32)->default('user')->after('password');
        });

        DB::table('users')->update([
            'role_old' => DB::raw("CASE WHEN role = 'admin' THEN 'admin' ELSE 'user' END"),
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('role_old', 'role');
        });
    }
};
