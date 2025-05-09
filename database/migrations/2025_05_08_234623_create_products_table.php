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
        Schema::disableForeignKeyConstraints();

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->string('name');
            $table->string('sku')->unique();
            $table->longText('description')->nullable();
            $table->decimal('purchase_price', 8, 2)->default(0.00);
            $table->decimal('sale_price', 8, 2)->default(0.00);
            $table->integer('stock')->default(0);
            $table->string('featured_image')->nullable();
            $table->boolean('status')->default(false);
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('products');

        Schema::enableForeignKeyConstraints();
    }
};
