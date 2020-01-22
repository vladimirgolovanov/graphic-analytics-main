<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImagesSalesAttributions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('shutter_id');
            $table->timestamp('added_on', 0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::create('sales', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('image_id')->unsigned();
            $table->date('date');
            $table->decimal('price', 8, 2);
            $table->timestamps();

            $table->foreign('image_id')->references('id')->on('images');
        });

        Schema::create('attributions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('image_id')->unsigned();
            $table->text('caption');
            $table->text('keywords');
            $table->tinyInteger('keywords_count');
            $table->timestamps();

            $table->foreign('image_id')->references('id')->on('images');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attributions', function (Blueprint $table) {
            $table->dropForeign('attributions_image_id_foreign');
        });
        Schema::dropIfExists('attributions');

        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign('sales_image_id_foreign');
        });
        Schema::dropIfExists('sales');

        Schema::table('images', function (Blueprint $table) {
            $table->dropForeign('images_user_id_foreign');
        });
        Schema::dropIfExists('images');
    }
}
