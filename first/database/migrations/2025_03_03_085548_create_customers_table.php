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
    Schema::create('customers', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // Customer name
        $table->string('email')->unique(); // Unique email
        $table->string('phone'); // Phone number
        $table->string('address'); // Address field
        $table->string('password'); // Password field (hashed)
        $table->timestamps(); // Created_at and updated_at timestamps
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
