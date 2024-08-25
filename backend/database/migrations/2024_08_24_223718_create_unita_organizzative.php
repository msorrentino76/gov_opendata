<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\LegalEntity;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('organizative_units', function (Blueprint $table) {
            
            $table->id();
            $table->foreignIdFor(LegalEntity::class)->constrained();
            
            $table->string('cod_amm')->nullable();
            $table->string('cod_uni_ou')->nullable(); //->unique(); NON PUO' ESSERE UNIQUE A CAUSA DELLA CANCELLAZIONE LOGICA
            $table->string('cod_aoo')->nullable();
            $table->string('des_ou');
            $table->string('regione')->nullable();
            $table->string('provincia')->nullable();
            $table->string('comune')->nullable();
            $table->string('cap')->nullable();
            $table->string('indirizzo')->nullable();
            $table->string('tel')->nullable();
            $table->string('fax')->nullable();
            $table->string('mail1')->nullable();
            $table->string('mail2')->nullable();
            $table->string('mail3')->nullable();
            $table->string('nome_resp')->nullable();
            $table->string('cogn_resp')->nullable();
            $table->string('mail_resp')->nullable();
            $table->string('tel_resp')->nullable();
                
            $table->timestamps();
            $table->softDeletes();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizative_units');
    }
};
