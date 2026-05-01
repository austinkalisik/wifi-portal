<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('site_name');
            $table->string('contact_email')->nullable();
            $table->string('phone')->nullable();
            $table->string('timezone')->default('Pacific/Port_Moresby');
            $table->string('currency', 3)->default('PGK');
            $table->string('api_key')->unique();
            $table->timestamps();
        });

        Schema::create('branding_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('logo_url')->nullable();
            $table->string('primary_color', 20)->default('#2563eb');
            $table->string('accent_color', 20)->default('#0f172a');
            $table->string('welcome_headline')->default('Fast guest WiFi access');
            $table->text('welcome_message')->nullable();
            $table->string('terms_url')->nullable();
            $table->timestamps();
        });

        Schema::create('wifi_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('duration_minutes');
            $table->decimal('price', 10, 2)->default(0);
            $table->unsignedInteger('download_mbps')->default(10);
            $table->unsignedInteger('upload_mbps')->default(5);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('routers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('vendor')->default('Generic');
            $table->string('mac_address')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('shared_secret');
            $table->string('status')->default('pending');
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });

        Schema::create('customer_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('router_id')->nullable()->constrained()->nullOnDelete();
            $table->string('mac_address');
            $table->string('label')->nullable();
            $table->string('status')->default('allowed');
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });

        Schema::create('guest_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('router_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('wifi_package_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('device_mac');
            $table->string('status')->default('active');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_sessions');
        Schema::dropIfExists('customer_devices');
        Schema::dropIfExists('routers');
        Schema::dropIfExists('wifi_packages');
        Schema::dropIfExists('branding_profiles');
        Schema::dropIfExists('businesses');
    }
};
