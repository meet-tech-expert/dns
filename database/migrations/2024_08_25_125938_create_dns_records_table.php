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
        if (!Schema::hasTable('dns_records')) {
            Schema::create('dns_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('domain_id')->constrained('domains');
                $table->enum('type', ['A', 'AAAA', 'CNAME', 'MX', 'TXT', 'SRV', 'NS']);
                $table->string('name', 255);
                $table->string('value', 255);
                $table->integer('ttl');
                $table->integer('priority')->nullable();
                $table->timestamps();
                $table->string('created_ip')->nullable();
                $table->string('updated_ip')->nullable();
                $table->integer('updates');
                $table->softDeletes();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('dns_records');
    }
};
