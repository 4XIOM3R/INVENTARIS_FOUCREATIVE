<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->uuid('onboarding_token')->unique();
            $table->string('company_name');
            $table->string('brand_name')->nullable();
            $table->string('established_year', 20)->nullable();
            $table->text('company_history')->nullable();
            $table->text('positioning_statement')->nullable();
            $table->json('target_market')->nullable();
            $table->string('nib', 100)->nullable();
            $table->date('nib_issued_date')->nullable();
            $table->date('nib_updated_date')->nullable();
            $table->string('investment_status', 100)->nullable();
            $table->string('npwp', 100)->nullable();
            $table->text('office_address')->nullable();
            $table->text('workshop_address')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('wa_business_number', 50)->nullable();
            $table->boolean('wa_active_7_days')->default(false);
            $table->string('system_email')->nullable();
            $table->string('nib_email')->nullable();
            $table->json('corporate_emails')->nullable();
            $table->string('website')->nullable();
            $table->json('social_media')->nullable();
            $table->enum('status', ['draft', 'in_progress', 'ready_to_deploy', 'completed'])->default('draft');
            $table->timestamps();
        });

        Schema::create('client_kblis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('kbli_code', 20);
            $table->string('title');
            $table->string('license_status', 150)->nullable();
            $table->text('location')->nullable();
            $table->timestamps();
        });

        Schema::create('client_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('platform');
            $table->string('username_or_email')->nullable();
            $table->text('secret_value')->nullable();
            $table->string('pic_name')->nullable();
            $table->string('pic_contact', 100)->nullable();
            $table->boolean('needs_new_domain')->default(false);
            $table->enum('verification_status', ['SUDAH LENGKAP', 'MASIH PROSES', 'BELUM DITERIMA'])->default('MASIH PROSES');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['client_id', 'platform']);
        });

        Schema::create('client_onboarding_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->string('item_name');
            $table->enum('status', ['SUDAH LENGKAP', 'MASIH PROSES', 'BELUM DITERIMA', 'MENYUSUL'])->default('MASIH PROSES');
            $table->text('detail_notes')->nullable();
            $table->string('file_path')->nullable();
            $table->string('billing_schedule')->nullable();
            $table->string('package_choice')->nullable();
            $table->boolean('has_chatbot_addon')->default(false);
            $table->timestamps();
        });

        Schema::create('client_knowledge_bases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('section_type');
            $table->string('category_label')->nullable();
            $table->string('title');
            $table->string('year', 20)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('client_technical_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('task_code', 20);
            $table->string('task_title');
            $table->boolean('is_completed')->default(false);
            $table->text('technical_notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['client_id', 'task_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_technical_checklists');
        Schema::dropIfExists('client_knowledge_bases');
        Schema::dropIfExists('client_onboarding_items');
        Schema::dropIfExists('client_credentials');
        Schema::dropIfExists('client_kblis');
        Schema::dropIfExists('clients');
    }
};
