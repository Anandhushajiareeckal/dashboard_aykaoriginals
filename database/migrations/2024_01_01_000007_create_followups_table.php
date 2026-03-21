<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('followups', function (Blueprint $table) {
            $table->id();
            $table->morphs('followupable');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('note');
            $table->date('followup_date')->nullable();
            $table->boolean('completed')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('followups'); }
};
