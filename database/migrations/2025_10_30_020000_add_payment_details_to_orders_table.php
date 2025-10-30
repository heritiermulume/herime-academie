<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table('orders', function (Blueprint $table) {
			$table->string('payment_currency')->nullable()->after('currency');
			$table->decimal('payment_amount', 12, 2)->nullable()->after('payment_currency');
			$table->decimal('exchange_rate', 16, 8)->nullable()->after('payment_amount');
			$table->string('payer_phone')->nullable()->after('payment_provider');
			$table->string('payer_country')->nullable()->after('payer_phone');
			$table->string('customer_ip', 45)->nullable()->after('payer_country');
			$table->text('user_agent')->nullable()->after('customer_ip');
			$table->decimal('provider_fee', 12, 2)->nullable()->after('total');
			$table->decimal('net_total', 12, 2)->nullable()->after('provider_fee');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('orders', function (Blueprint $table) {
			$table->dropColumn([
				'payment_currency',
				'payment_amount',
				'exchange_rate',
				'payer_phone',
				'payer_country',
				'customer_ip',
				'user_agent',
				'provider_fee',
				'net_total',
			]);
		});
	}
};


