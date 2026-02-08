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
        Schema::table('users', function (Blueprint $table) {
            $table->string('stripe_customer_id')->nullable()->index();
            $table->string('stripe_subscription_id')->nullable()->index();

            // Subscription info (cached from Stripe for fast gating)
            $table->string('stripe_price_id')->nullable();          // e.g. price_123
            $table->string('stripe_status')->nullable()->index();   // active, trialing, past_due, canceled, etc.
            $table->timestamp('stripe_current_period_end')->nullable();
            $table->boolean('stripe_cancel_at_period_end')->default(false);

            // Optional (if you ever use trials)
            $table->timestamp('stripe_trial_ends_at')->nullable();

            // Your app entitlement (simple + stable)
            $table->string('plan')->default('free')->index();       // free|pro
            $table->timestamp('plan_expires_at')->nullable();       // optional grace period handling
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_customer_id',
                'stripe_subscription_id',
                'stripe_price_id',
                'stripe_status',
                'stripe_current_period_end',
                'stripe_cancel_at_period_end',
                'stripe_trial_ends_at',
                'plan',
                'plan_expires_at',
            ]);
        });
    }
};
