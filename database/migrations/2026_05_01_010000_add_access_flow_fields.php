<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wifi_packages', function (Blueprint $table) {
            $table->string('access_type')->default('subscription')->after('name');
            $table->text('description')->nullable()->after('access_type');
            $table->unsignedInteger('ad_watch_seconds')->nullable()->after('duration_minutes');
        });

        Schema::table('guest_sessions', function (Blueprint $table) {
            $table->string('access_method')->default('subscription')->after('wifi_package_id');
            $table->string('payment_reference')->nullable()->after('phone');
            $table->decimal('amount_paid', 10, 2)->default(0)->after('payment_reference');
        });
    }

    public function down(): void
    {
        Schema::table('guest_sessions', function (Blueprint $table) {
            $table->dropColumn(['access_method', 'payment_reference', 'amount_paid']);
        });

        Schema::table('wifi_packages', function (Blueprint $table) {
            $table->dropColumn(['access_type', 'description', 'ad_watch_seconds']);
        });
    }
};
