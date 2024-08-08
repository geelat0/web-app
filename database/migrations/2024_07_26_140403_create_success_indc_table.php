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
            $table->string('target')->default('0');
            $table->string('Albay_target')->nullable();
            $table->string('Camarines_Sur_target')->nullable();
            $table->string('Camarines_Norte_target')->nullable();
            $table->string('Catanduanes_target')->nullable();
            $table->string('Masbate_target')->nullable();
            $table->string('Sorsogon_target')->nullable();
            $table->unsignedBigInteger('Albay_budget')->nullable();
            $table->unsignedBigInteger('Camarines_Sur_budget')->nullable();
            $table->unsignedBigInteger('Camarines_Norte_budget')->nullable();
            $table->unsignedBigInteger('Catanduanes_budget')->nullable();
            $table->unsignedBigInteger('Masbate_budget')->nullable();
            $table->unsignedBigInteger('Sorsogon_budget')->nullable();
            $table->string('measures');
            $table->unsignedBigInteger('alloted_budget');
            $table->json('division_id')->nullable();
            $table->string('status')->default('Active');
            $table->string('created_by');
            $table->string('updated_by')->nullable();
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
