<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::table('orders', function (Blueprint $table) {
			$table->string('provider_fee_currency', 16)->nullable()->after('provider_fee');
		});
	}

	public function down(): void
	{
		Schema::table('orders', function (Blueprint $table) {
			$table->dropColumn('provider_fee_currency');
		});
	}
};


