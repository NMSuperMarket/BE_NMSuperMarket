<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. users
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('username', 50)->unique();
            $table->string('email', 150)->unique();
            $table->string('password', 255)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('avatar', 500)->nullable();
            $table->enum('role', ['admin', 'customer', 'shipper'])->default('customer');
            $table->string('firebase_uid', 128)->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
        });

        // 2. categories
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->text('description')->nullable();
            $table->string('icon', 100)->nullable();
            $table->string('image', 500)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. products
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->string('name', 200);
            $table->string('slug', 220)->unique();
            $table->string('sku', 50)->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 15, 2);
            $table->decimal('sale_price', 15, 2)->nullable();
            $table->string('unit', 30)->default('cái');
            $table->string('thumbnail', 500)->nullable();
            $table->string('brand', 100)->nullable();
            $table->string('origin', 100)->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sold_count')->default(0);
            $table->timestamps();
            
            $table->foreign('category_id')->references('id')->on('categories');
        });

        // 4. product_images
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('url', 500);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });

        // 5. inventory
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->unique();
            $table->integer('quantity')->default(0);
            $table->integer('reserved')->default(0);
            $table->integer('low_stock_alert')->default(10);
            $table->timestamps();
            
            $table->foreign('product_id')->references('id')->on('products');
        });

        // 6. inventory_logs
        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->enum('type', ['in', 'out', 'adjust']);
            $table->integer('quantity');
            $table->string('reference', 100)->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            $table->foreign('product_id')->references('id')->on('products');
        });

        // 7. carts
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 8. cart_items
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cart_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity')->default(1);
            $table->decimal('price', 15, 2);
            $table->timestamps();
            
            $table->unique(['cart_id', 'product_id']);
            $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products');
        });

        // 9. orders
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code', 20)->unique();
            $table->unsignedBigInteger('user_id');
            $table->enum('status', ['pending', 'confirmed', 'preparing', 'shipping', 'delivered', 'cancelled'])->default('pending');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('shipping_fee', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->enum('payment_method', ['cod', 'vnpay', 'momo']);
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid');
            $table->string('shipping_name', 100);
            $table->string('shipping_phone', 20);
            $table->string('shipping_address', 255);
            $table->string('shipping_ward', 100)->nullable();
            $table->string('shipping_district', 100)->nullable();
            $table->string('shipping_province', 100)->nullable();
            $table->unsignedBigInteger('shipper_id')->nullable();
            $table->enum('shipping_provider', ['internal', 'ghn', 'ghtk'])->default('internal');
            $table->string('tracking_code', 100)->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users');
        });

        // 10. order_items
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id');
            $table->string('product_name', 200);
            $table->string('product_sku', 50);
            $table->string('thumbnail', 500)->nullable();
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_price', 15, 2);
            
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products');
        });

        // 11. payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->enum('method', ['cod', 'vnpay', 'momo']);
            $table->enum('status', ['pending', 'success', 'failed', 'cancelled', 'refunded'])->default('pending');
            $table->decimal('amount', 15, 2);
            $table->string('transaction_id', 100)->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            $table->foreign('order_id')->references('id')->on('orders');
        });

        // 12. invoices
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->unique();
            $table->string('invoice_code', 20)->unique();
            $table->timestamp('issued_at')->useCurrent();
            
            $table->foreign('order_id')->references('id')->on('orders');
        });
        
        // Sanctum/Passport requirements if using JWT we don't strictly need personal_access_tokens, 
        // but let's add it just in case Laravel defaults require it.
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('inventory_logs');
        Schema::dropIfExists('inventory');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('users');
    }
};
