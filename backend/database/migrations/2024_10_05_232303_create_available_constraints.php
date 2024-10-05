<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Dataflow;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('available_constraints', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Dataflow::class)->constrained();
            $table->string('flow_ref');
            $table->string('data_struct');            
            $table->string('key');
            $table->json('json_value');  
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('available_constraints');
    }
};
