<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class Projects.
 *
 * @author  The scaffold-interface created at 2017-08-30 01:23:01pm
 * @link  https://github.com/amranidev/scaffold-interface
 */
class Projects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::create('projects',function (Blueprint $table){

        $table->increments('id');
        
        $table->String('url')->unique();
        
        /**
         * Foreignkeys section
         */
        
        
        $table->timestamps();
        
        
        $table->softDeletes();
        
        // type your addition here

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::drop('projects');
    }
}
