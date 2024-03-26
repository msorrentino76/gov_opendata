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
        Schema::create('quotes', function (Blueprint $table) {
            
            $table->id();
            
            $table->timestamp('periodo_da');
            $table->timestamp('periodo_a');
            
            $table->decimal('importo_totale'            , 8, 2);
            $table->decimal('dividendo_vendita_totale'  , 8, 2);
            $table->decimal('dividendo_attivita_totale' , 8, 2);
            $table->decimal('importo_residuo_cassa'     , 8, 2);
            
            $table->timestamps();
            $table->softDeletes();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
