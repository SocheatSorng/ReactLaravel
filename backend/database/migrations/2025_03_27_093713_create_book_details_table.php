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
        Schema::create('book_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->unique()->constrained('books')->onDelete('cascade');
            $table->string('isbn10', 10)->nullable();
            $table->string('isbn13', 17)->nullable();
            $table->string('publisher', 255)->nullable();
            $table->integer('publish_year')->nullable();
            $table->string('edition', 50)->nullable();
            $table->integer('page_count')->nullable();
            $table->string('language', 50)->nullable();
            $table->enum('format', ['Hardcover', 'Paperback', 'Ebook', 'Audiobook'])->nullable();
            $table->string('dimensions', 100)->nullable();
            $table->decimal('weight', 6, 2)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_details');
    }
};
