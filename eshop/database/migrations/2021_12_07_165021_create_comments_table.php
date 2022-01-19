<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('commenter_id');
            $table->foreign('product_id')->references('id')->on('products')
                ->cascadeOnDelete();
            $table->foreign('commenter_id')->references('id')->on('users')->nullOnDelete();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->longText('content');
            $table->timestamps();

            //only for sqlite
            if (env('DB_CONNECTION') == 'sqlite') {
                //creating a self-referencing column
                $table->foreign('parent_id')->references('id')->on('comments')
                    ->cascadeOnDelete();
            }
        });
        
        //creating a self-referencing column
        Schema::table('comments', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('comments')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comments');
    }
}
