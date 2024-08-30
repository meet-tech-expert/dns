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
        if (!Schema::hasTable('domains')) {
            Schema::create('domains', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->onUpdate('cascade');
                $table->string('domain_name', 255)->unique();
                $table->enum('status', ['pending', 'active', 'failed']);
                $table->timestamps();
                $table->timestamp('created_ip')->nullable();
                $table->timestamp('updated_ip')->nullable();
                $table->integer('updates')->default(0);
            });    
        }
    }

    public function down()
    {
        Schema::dropIfExists('domains');
    }
};
