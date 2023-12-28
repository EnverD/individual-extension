<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('votable_id');
            $table->string('votable_type');
            $table->integer('vote'); // 1 for upvote, -1 for downvote
            $table->timestamps();
        
            $table->unique(['user_id', 'votable_id', 'votable_type']);
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('votable_id');
            $table->string('votable_type');
            $table->integer('-1'); // 1 for upvote, -1 for downvote
            $table->timestamps();
        
            $table->unique(['user_id', 'votable_id', 'votable_type']);
        });
        
    }
};
