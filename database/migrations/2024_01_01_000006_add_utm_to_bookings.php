<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // UTM tracking fields
            $table->string('utm_source',   100)->nullable()->after('razorpay_payment_id');
            $table->string('utm_medium',   100)->nullable()->after('utm_source');
            $table->string('utm_campaign', 200)->nullable()->after('utm_medium');
            $table->string('utm_content',  200)->nullable()->after('utm_campaign');
            $table->string('utm_term',     200)->nullable()->after('utm_content');
            // Referrer & landing page
            $table->string('referrer',     500)->nullable()->after('utm_term');
            $table->string('landing_page', 500)->nullable()->after('referrer');

            // Index for source reporting
            $table->index('utm_source', 'idx_bookings_utm_source');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('idx_bookings_utm_source');
            $table->dropColumn([
                'utm_source', 'utm_medium', 'utm_campaign',
                'utm_content', 'utm_term', 'referrer', 'landing_page',
            ]);
        });
    }
};
