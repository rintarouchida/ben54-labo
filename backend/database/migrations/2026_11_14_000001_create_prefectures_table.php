<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prefectures', function (Blueprint $table): void {
            // JISの都道府県コード(1〜47)をそのまま入れるため自動採番にしない
            $table->unsignedTinyInteger('id')->primary();
            $table->string('name', 10)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prefectures');
    }
};
