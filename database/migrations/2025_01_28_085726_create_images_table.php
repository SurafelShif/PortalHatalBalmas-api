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
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('imageable');
            $table->string("image_name")->nullable();
            $table->string("image_path")->nullable();
            $table->string("image_type")->nullable();
            $table->string("image_file_name")->nullable();
            $table->boolean("is_commited")->default(false);
            $table->string('type')->default('content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
