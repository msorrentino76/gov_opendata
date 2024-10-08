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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('email')->unique();
            $table->string('name');
            $table->string('surname');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->text('abilities');
            $table->boolean('password_changed')->nullable(true);	
            $table->boolean('notify_email')->nullable(true); 
            $table->boolean('enabled')->nullable(false)->default(true); 
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
