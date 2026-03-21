<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->string('category')->nullable();
            $table->decimal('budget', 12, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['Planning', 'Active', 'Review', 'Completed', 'Cancelled'])->default('Planning');
            $table->integer('progress')->default(0);
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('project_model', function (Blueprint $table) {
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('talent_model_id')->constrained('talent_models')->cascadeOnDelete();
            $table->primary(['project_id', 'talent_model_id']);
        });

        Schema::create('project_crew', function (Blueprint $table) {
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('crew_id');
            $table->foreign('crew_id')->references('id')->on('crew')->onDelete('cascade');
            $table->primary(['project_id', 'crew_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_crew');
        Schema::dropIfExists('project_model');
        Schema::dropIfExists('projects');
    }
};