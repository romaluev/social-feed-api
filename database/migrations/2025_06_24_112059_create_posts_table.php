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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->decimal('hotness', 8, 2)->default(0)->index();
            $table->unsignedBigInteger('view_count')->default(0)->index();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['hotness', 'created_at']);
            $table->index(['view_count', 'hotness']);
            $table->index(['user_id', 'hotness']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
