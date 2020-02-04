<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class NewImageFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->string('page_url')->after('shutter_id');
            $table->string('image_url')->after('page_url');
        });

        Schema::create('keywords', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('word')->unique();
            $table->timestamps();
        });

        Schema::create('attribution_keyword', function (Blueprint $table) {
            $table->bigInteger('attribution_id')->unsigned();
            $table->bigInteger('keyword_id')->unsigned();
            $table->tinyInteger('selling')->default(0);
            $table->tinyInteger('order');
            $table->timestamps();

            $table->foreign('attribution_id')->references('id')->on('attributions');
            $table->foreign('keyword_id')->references('id')->on('keywords');
        });

        Schema::table('attributions', function (Blueprint $table) {
            $table->dropColumn(['keywords']);
            $table->tinyInteger('selling_keywords_count')->default(0)->after('keywords_count');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('attributions', function (Blueprint $table) {
            $table->dropColumn('selling_keywords_count');
            $table->text('keywords')->nullable()->after('caption');
        });

        Schema::table('attribution_keyword', function (Blueprint $table) {
            $table->dropForeign('attribution_keyword_attribution_id_foreign');
            $table->dropForeign('attribution_keyword_keyword_id_foreign');
        });
        Schema::dropIfExists('attribution_keyword');

        Schema::dropIfExists('keywords');

        Schema::table('images', function (Blueprint $table) {
            $table->dropColumn(['page_url', 'image_url']);
        });
    }
}
