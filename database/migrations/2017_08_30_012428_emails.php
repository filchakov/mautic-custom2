<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class Emails.
 *
 * @author  The scaffold-interface created at 2017-08-30 01:24:28pm
 * @link  https://github.com/amranidev/scaffold-interface
 */
class Emails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::create('emails',function (Blueprint $table){

        $table->increments('id');
        
        $table->String('title');
        
        $table->longText('body');
        
        $table->integer('mautic_email_id');
        
        $table->integer('project_id')->unsigned();
        $table->foreign('project_id')->references('id')->on('projects');

        /**
         * Foreignkeys section
         */

        $table->timestamps();
        $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::drop('emails');
    }
}
