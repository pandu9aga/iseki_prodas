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
        Schema::create('ng_processes', function (Blueprint $table) {
            $table->id();
            $table->string('app_name', 50);
            $table->string('sequence_no', 50);
            $table->string('current_process', 100)->nullable();
            $table->string('missing_process', 255);
            $table->text('message')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ng_processes');
    }
};
