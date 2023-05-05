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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name',255)->nullable();
            $table->string('sku', 255)->nullable();
            $table->string('status', 255)->nullable();
            $table->text('variations')->nullable(); // based on the column of variations that I'v found in the csv file
            $table->decimal('price',13,2)->nullable();
            $table->string('currency',20)->nullable();
            $table->integer('quantity')->nullable();
            $table->boolean('deleted_by_sync')->default(false)->nullable(); //Hint that this is deleted by sync
            $table->softDeletes(); // Adds a `deleted_at` column to the `products` table
            $table->timestamps();
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
