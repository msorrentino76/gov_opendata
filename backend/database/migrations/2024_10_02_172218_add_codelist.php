<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Codelist;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('codelists', function (Blueprint $table) {
            
            $table->id();

            // not nullable di default
            $table->string('codelist')->unique();
            $table->string('urn');
            $table->string('version');
            $table->string('name');
            
            $table->timestamps();
            
        });
        
        Schema::create('codes', function (Blueprint $table) {
            
            $table->id();

            $table->foreignIdFor(Codelist::class)->constrained();
            $table->string('codelist');
            $table->string('code');
            $table->string('name', 1024);
            
            $table->timestamps();
            
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('code');
        Schema::dropIfExists('codelist');
    }
};
