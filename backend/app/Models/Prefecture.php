<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PrefectureFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prefecture extends Model
{
    /** @use HasFactory<PrefectureFactory> */
    use HasFactory;

    // id は JIS の都道府県コードをそのまま使う
    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = ['id', 'name'];

    /**
     * @return HasMany<LawOffice, $this>
     */
    public function lawOffices(): HasMany
    {
        return $this->hasMany(LawOffice::class);
    }
}
