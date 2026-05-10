<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Performance indexes for booking search & reporting
        Schema::table('bookings', function (Blueprint $table) {
            $table->index('phone',      'idx_bookings_phone');
            $table->index('email',      'idx_bookings_email');
            $table->index('status',     'idx_bookings_status');
            $table->index('created_at', 'idx_bookings_created_at');
        });

        // Slot date lookup (most common query)
        Schema::table('slots', function (Blueprint $table) {
            $table->index('date',                  'idx_slots_date');
            $table->index(['date', 'is_active'],   'idx_slots_date_active');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('idx_bookings_phone');
            $table->dropIndex('idx_bookings_email');
            $table->dropIndex('idx_bookings_status');
            $table->dropIndex('idx_bookings_created_at');
        });

        Schema::table('slots', function (Blueprint $table) {
            $table->dropIndex('idx_slots_date');
            $table->dropIndex('idx_slots_date_active');
        });
    }
};
