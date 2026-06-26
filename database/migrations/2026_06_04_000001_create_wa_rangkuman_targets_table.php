<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('wa_rangkuman_targets', function (Blueprint $table) {
            $table->id('Id_Target');
            $table->date('Target_Date');
            $table->string('Category_Group', 50);        // TRANSMISI, SUB ENGINE, LINE A, etc
            $table->string('Category_Item', 50);          // SXG3 & SF, Transmisi, Unit, Mocol, etc
            $table->integer('Target')->default(0);
            $table->datetime('Created_At')->nullable();
            $table->datetime('Updated_At')->nullable();
            
            $table->unique(['Target_Date', 'Category_Group', 'Category_Item'], 'wa_target_unique');
        });

        Schema::create('wa_rangkuman_logs', function (Blueprint $table) {
            $table->id('Id_Log');
            $table->string('Action_Type', 20);            // IMPORT, EXPORT, UPDATE
            $table->string('File_Name', 255)->nullable();
            $table->integer('Total_Rows')->default(0);
            $table->string('Month', 7)->nullable();       // YYYY-MM
            $table->integer('Created_By')->nullable();
            $table->datetime('Created_At')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('wa_rangkuman_targets');
        Schema::dropIfExists('wa_rangkuman_logs');
    }
};
