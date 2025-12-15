<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('group_name'); // 'Skill Level', 'Grades', 'Others'
            $table->integer('value'); // Numeric value for ordering/comparison
            $table->integer('order')->default(0); // Display order
        });
    }

    public function down()
    {
        Schema::dropIfExists('levels');
    }
};
