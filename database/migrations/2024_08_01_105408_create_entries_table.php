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
        Schema::create('entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('indicator_id');
            $table->longText('file')->nullable();
            $table->unsignedBigInteger('months');
            $table->string('status')->default('Active');
            $table->string('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('indicator_id')->references('id')->on('success_indc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entries');
    }
};
