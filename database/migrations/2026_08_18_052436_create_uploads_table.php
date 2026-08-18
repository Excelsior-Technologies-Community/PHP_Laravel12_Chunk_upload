<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uploads', function (Blueprint $table) {
            $table->id();

            $table->string('file_name');
            $table->string('original_name');

            $table->string('file_path');

            $table->string('mime_type')->nullable();
            $table->string('extension')->nullable();

            $table->unsignedBigInteger('file_size')->default(0);

            $table->string('status')->default('completed');

            $table->timestamps();

            $table->index('original_name');
            $table->index('status');
            $table->index('extension');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uploads');
    }
};
