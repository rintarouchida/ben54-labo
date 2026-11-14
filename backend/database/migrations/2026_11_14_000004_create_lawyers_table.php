<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lawyers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('law_office_id')->index();
            $table->string('name', 50);
            $table->string('name_kana', 100)->index();
            $table->date('birth_date')->index();
            $table->unsignedSmallInteger('registered_year');
            $table->unsignedInteger('case_count')->default(0)->index();
            $table->text('career');
            $table->text('introduction');
            $table->timestamps();

            $table->foreign('law_office_id')->references('id')->on('law_offices');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lawyers');
    }
};
