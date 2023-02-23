<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tokens', function (Blueprint $table) {
            $table->string('ip', 50)->after('user_id')->nullable();
            $table->string('device', 200)->after('jti')->nullable();
            $table->json('grants')->after('payload')->default('[]');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tokens', function (Blueprint $table) {
            $table->dropColumn('ip');
            $table->dropColumn('device');
            $table->dropColumn('grants');
        });
    }
};
