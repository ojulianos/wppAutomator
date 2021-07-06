<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('message_id')->nullable();
            $table->foreign('message_id')->references('id')->on('messages');
            $table->integer('reference_id')->nullable();
            $table->unsignedBigInteger('reference_phone');
//            $table->foreign('reference_phone')->references('phone_number')->on('phones');
            $table->string('description', 250);
            $table->text('body');
            $table->text('tags')->nullable();
            $table->enum('type', [
                'primeiro-contato',
                'mensagem',
                'resultados',
                'nao-encontrato',
                'finalizacao',
            ])->default('mensagem');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('message');
    }
}
