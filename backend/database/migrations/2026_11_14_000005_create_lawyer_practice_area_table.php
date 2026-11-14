<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lawyer_practice_area', function (Blueprint $table): void {
            $table->unsignedBigInteger('lawyer_id');
            $table->unsignedBigInteger('practice_area_id');

            // 同じ弁護士に同じ分野を重複登録させない
            $table->primary(['lawyer_id', 'practice_area_id']);
            // 分野での絞り込み(whereHas + whereIn)用
            $table->index(['practice_area_id', 'lawyer_id']);

            $table->foreign('lawyer_id')->references('id')->on('lawyers')->cascadeOnDelete();
            $table->foreign('practice_area_id')->references('id')->on('practice_areas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lawyer_practice_area');
    }
};
