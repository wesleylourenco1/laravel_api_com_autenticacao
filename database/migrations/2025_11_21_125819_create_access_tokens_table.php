<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
        /**
         * Run the migrations.
         */
    public function up()
        {
            Schema::create('access_tokens', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('client_id');
                $table->string('token')->unique();
                $table->timestamp('expires_at');
                $table->timestamps();

                $table->foreign('client_id')
                    ->references('id')
                    ->on('clients')
                    ->onDelete('cascade');
            });
}

public function down()
    {
        Schema::dropIfExists('access_tokens');
    }


};
