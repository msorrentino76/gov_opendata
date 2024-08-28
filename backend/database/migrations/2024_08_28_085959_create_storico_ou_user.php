<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\OrganizativeUnit;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('storico_ou_user', function (Blueprint $table) {
            
            $table->id();

            $table->foreignIdFor(OrganizativeUnit::class)->constrained();
            $table->foreignIdFor(User::class)->constrained();
            $table->string('action');
            $table->string('users');
            $table->timestamp('performed_at');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storico_ou_user');
    }
};
