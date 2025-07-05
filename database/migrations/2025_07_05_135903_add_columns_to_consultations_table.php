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
        Schema::table('consultations', function (Blueprint $table) {
            $table->longText('prescription')->nullable()->after('doctor_id');
            $table->string('status')->nullable()->after('prescription');
            $table->string('order_type')->nullable()->after('status');
            $table->string('order_source')->nullable()->after('status');
            $table->string('order_number')->nullable()->after('order_source');
            $table->double('order_total')->nullable()->after('order_source');
            $table->string('order_address')->nullable()->after('order_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            //
        });
    }
};
