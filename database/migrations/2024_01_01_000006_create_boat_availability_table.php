<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boat_availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boat_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->boolean('is_available')->default(true);
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->unique(['boat_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boat_availability');
    }
};
