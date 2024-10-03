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
        Schema::create('dataflow', function (Blueprint $table) {
            $table->id();
            $table->string('flow_ref')->unique();
            $table->string('category');
            $table->string('data_struct');
            $table->boolean('is_final');
            $table->string('name');
            $table->string('version');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dataflow');
    }
};
