<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            
            //$table->uuid('id')->primary();            
            $table->id();            
            
            //$table->morphs('documentable');
            $table->nullableMorphs('documentable');
            
            $table->string('name');
            $table->unsignedBigInteger('size');
            $table->string('mime');
            $table->binary('content');
             
            $table->string('description')->nullable();
            
            $table->foreignIdFor(User::class)->constrained();
            
            $table->timestamps();
            $table->softDeletes();
        });
        
        DB::statement("ALTER TABLE documents MODIFY content LONGBLOB");
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
