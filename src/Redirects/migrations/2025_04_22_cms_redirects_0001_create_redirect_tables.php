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
        Schema::create('cms_redirects', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->boolean('enabled')->default(false);
            $table->text('group')->nullable();
            $table->string('source_type');
            $table->string('source', 2048);
            $table->text('target');
            $table->smallInteger('code');
            $table->text('description')->nullable();

            $table->index(['source_type', 'source']);
        });

        Schema::create('cms_redirect_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('url', 2048);
            $table->string('path', 2048);
            $table->foreignId('redirect_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cms_redirects');
        Schema::dropIfExists('cms_redirect_logs');
    }
};
