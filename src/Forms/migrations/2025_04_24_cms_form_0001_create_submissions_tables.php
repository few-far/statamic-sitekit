<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cms_form_submissions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('email')->nullable();
            $table->foreignId('form_id')->index();
            $table->jsonb('values')->nullable();
            $table->jsonb('meta')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cms_form_submissions');
    }
};
