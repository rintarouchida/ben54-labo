<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\LawOfficeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LawOffice extends Model
{
    /** @use HasFactory<LawOfficeFactory> */
    use HasFactory;

    protected $fillable = ['prefecture_id', 'name', 'address', 'phone_number'];

    /**
     * @return BelongsTo<Prefecture, $this>
     */
    public function prefecture(): BelongsTo
    {
        return $this->belongsTo(Prefecture::class);
    }

    /**
     * @return HasMany<Lawyer, $this>
     */
    public function lawyers(): HasMany
    {
        return $this->hasMany(Lawyer::class);
    }
}
