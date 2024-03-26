<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\User;
use App\Models\Quote;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quote_details', function (Blueprint $table) {
            
            $table->id();
            
            $table->foreignIdFor(Quote::class)->constrained();
            
            $table->foreignIdFor(User::class)->constrained();
            $table->decimal('percentuale_vendita' , 5, 2);
            $table->decimal('dividendo_vendita'   , 8, 2);
            $table->decimal('percentuale_attivita', 5, 2);
            $table->decimal('dividendo_attivita'  , 8, 2);
            
            $table->timestamps();
            $table->softDeletes();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_details');
    }
};
