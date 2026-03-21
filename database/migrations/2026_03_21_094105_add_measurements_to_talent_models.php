<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('talent_models', function (Blueprint $table) {
            if (!Schema::hasColumn('talent_models', 'height'))    $table->string('height')->nullable()->after('age');
            if (!Schema::hasColumn('talent_models', 'bust'))      $table->string('bust')->nullable()->after('height');
            if (!Schema::hasColumn('talent_models', 'waist'))     $table->string('waist')->nullable()->after('bust');
            if (!Schema::hasColumn('talent_models', 'hips'))      $table->string('hips')->nullable()->after('waist');
            if (!Schema::hasColumn('talent_models', 'shoe_size')) $table->string('shoe_size')->nullable()->after('hips');
        });
    }

    public function down(): void
    {
        Schema::table('talent_models', function (Blueprint $table) {
            $table->dropColumn(['height', 'bust', 'waist', 'hips', 'shoe_size']);
        });
    }
};
