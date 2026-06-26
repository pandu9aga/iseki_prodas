<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('wa_rangkuman_history', function (Blueprint $table) {
            $table->id('Id_History');
            $table->date('Log_Date');
            $table->string('Category_Group', 50);
            $table->string('Category_Item', 50);
            $table->integer('Target')->default(0);
            $table->integer('Actual')->default(0);
            $table->integer('Selisih')->default(0);
            $table->integer('Grand_Total')->default(0);
            $table->text('Koreksi')->nullable();
            $table->datetime('Created_At')->nullable();
            
            $table->unique(['Log_Date', 'Category_Group', 'Category_Item'], 'wa_history_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('wa_rangkuman_history');
    }
};
