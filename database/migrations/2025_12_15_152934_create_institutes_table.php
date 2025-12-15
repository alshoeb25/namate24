<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('institutes', function (Blueprint $table) {
            $table->id();

            $table->string('ugc_code')->nullable()->index();
            $table->string('name')->index();

            $table->string('type')->nullable(); 
            // Central | State | Deemed | Private

            $table->string('state')->nullable()->index();
            $table->string('city')->nullable()->index();
            $table->string('region')->nullable()->index();

            $table->boolean('is_ugc_approved')->default(true);
            $table->boolean('status')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institutes');
    }
};
