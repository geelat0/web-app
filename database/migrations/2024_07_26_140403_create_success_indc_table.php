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
        Schema::create('success_indc', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('org_id');
            $table->string('target');
            $table->string('measures');
            $table->string('status')->default('Active');
            $table->string('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('org_id')->references('id')->on('org_otc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('success_indc');
    }
};
