<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('law_offices', function (Blueprint $table): void {
            $table->id();
            $table->unsignedTinyInteger('prefecture_id')->index();
            $table->string('name', 100);
            $table->string('address', 255);
            $table->string('phone_number', 20);
            $table->timestamps();

            $table->foreign('prefecture_id')->references('id')->on('prefectures');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('law_offices');
    }
};
