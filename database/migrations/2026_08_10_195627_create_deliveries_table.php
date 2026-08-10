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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sale_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('customer_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            $table->string('delivery_address');

            $table->string('recipient_name');

            $table->string('recipient_phone', 20);

            $table->decimal('delivery_fee', 12, 2)->default(0);

            $table->enum('status', [
                'pending',
                'assigned',
                'out_for_delivery',
                'delivered',
                'cancelled'
            ])->default('pending');

            $table->foreignId('assigned_to')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('scheduled_at')->nullable();

            $table->timestamp('delivered_at')->nullable();

            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};