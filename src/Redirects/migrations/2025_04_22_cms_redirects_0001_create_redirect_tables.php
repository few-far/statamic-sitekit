<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
            $table->jsonb('description')->nullable();

            $table->index(['source_type', 'source']);
            $table->index(['source_type', DB::raw('LOWER(source)')], 'cms_redirects_source_type_lower_source_index');
        });

        Schema::create('cms_redirect_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text('url');
            $table->text('path');
            $table->foreignId('redirect_id')->nullable();
            $table->text('redirect')->nullable();
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
