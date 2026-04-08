<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('comp_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('talent_model_id')->constrained('talent_models')->cascadeOnDelete();
            $table->string('template')->default('noir');
            $table->string('title')->nullable();
            $table->string('agency_name')->default('Ayka Originals');
            $table->string('agency_phone')->nullable();
            $table->string('agency_email')->nullable();
            $table->string('agency_website')->nullable();
            $table->text('notable_clients')->nullable();
            $table->text('recent_campaigns')->nullable();
            $table->text('special_skills')->nullable();
            $table->decimal('day_rate', 10, 2)->nullable();
            $table->decimal('half_day_rate', 10, 2)->nullable();
            $table->json('available_for')->nullable();
            $table->string('public_slug')->unique()->nullable();
            $table->unsignedInteger('view_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('comp_card_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comp_card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sent_by')->constrained('users')->cascadeOnDelete();
            $table->string('recipient_email');
            $table->string('recipient_name')->nullable();
            $table->string('subject');
            $table->text('message');
            $table->boolean('attach_pdf')->default(true);
            $table->boolean('attach_portfolio')->default(true);
            $table->boolean('attach_photos_zip')->default(false);
            $table->timestamp('opened_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('comp_card_shares');
        Schema::dropIfExists('comp_cards');
    }
};