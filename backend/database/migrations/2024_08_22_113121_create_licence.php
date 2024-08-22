<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\User;
use App\Models\LegalEntity;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('licences', function (Blueprint $table) {
            
            $table->id();
            
            $table->foreignIdFor(User::class)->constrained();
            $table->foreignIdFor(LegalEntity::class)->constrained();
            
            $table->timestamp('valida_da');
            $table->timestamp('valida_a');
            
            $table->timestamps();
            $table->softDeletes();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licence');
    }
};
