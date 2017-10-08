<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCompanyTableWithUserData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('auth_key', 100);
            $table->string('cooked_key', 100);
            $table->string('telegram_first_name');
            $table->string('telegram_last_name');
            $table->text('telegram_auth_data');
            $table->bigInteger('telegram_id')->unsigned()->default(0);
            $table->index(['telegram_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropIndex('telegram_id');
            $table->dropColumn(['telegram_id', 'cooked_key', 'auth_key','telegram_first_name',
                'telegram_last_name', 'telegram_auth_data']);
        });
    }
}
