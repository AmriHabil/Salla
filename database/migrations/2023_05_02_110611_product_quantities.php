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
        Schema::create('product_quantities', function (Blueprint $table) {
            $table->id();
            $table->string('product_id',255)->nullable();
            $table->text('variations')->nullable(); // Regarding the response of the endpoint majority  of products
            // quantities depends on color & material or color & size and it may even depend only on a single attribute
            // SO THIS SHOULD BE FLEXIBLE
            $table->float('quantity')->nullable(); // I prefer keeping quantity of each variation in separated
            //column so it should be better for counting the total number of pieces of each product
            
            $table->timestamps();
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_quantities');
    }
};
