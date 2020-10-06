<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\SalesType;

class AddSalesTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->integer('sales_type_id')->unsigned()->after('price');

            $table->foreign('sales_type_id')->references('id')->on('sales_types');
        });

        SalesType::insert([
            ['id' => 1, 'name' => 'Subscriptions',],
            ['id' => 2, 'name' => 'On demand',],
            ['id' => 3, 'name' => 'Enhanced',],
            ['id' => 4, 'name' => 'Cart sales',],
            ['id' => 5, 'name' => 'Clip packs',],
            ['id' => 6, 'name' => 'Single & other',],
            ['id' => 7, 'name' => 'Referrals',],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign('sales_sales_type_id_foreign');
            $table->dropColumn('sales_type_id');
        });
        Schema::dropIfExists('sales_types');
    }
}
