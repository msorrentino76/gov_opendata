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
        Schema::create('legal_entities', function (Blueprint $table) {
            
            $table->id();
            
            $table->string('cod_amm')->nullable()->unique();
            $table->string('acronimo')->nullable();
            $table->string('des_amm');
            $table->string('regione')->nullable();
            $table->string('provincia')->nullable();
            $table->string('comune')->nullable();
            $table->string('cap')->nullable();
            $table->string('indirizzo')->nullable();
            $table->string('titolo_resp')->nullable();
            $table->string('nome_resp')->nullable();
            $table->string('cogn_resp')->nullable();
            $table->string('sito_istituzionale')->nullable();
            $table->string('liv_access')->nullable();
            $table->string('mail1')->nullable();
            $table->string('mail2')->nullable();
            $table->string('mail3')->nullable();
            $table->string('mail4')->nullable();
            $table->string('mail5')->nullable();
            $table->string('tipologia')->nullable();
            $table->string('categoria')->nullable();
            $table->string('data_accreditamento')->nullable();
            $table->string('cf')->unique();
            $table->string('telefono')->nullable();
            $table->string('fax')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_entities');
    }
};
