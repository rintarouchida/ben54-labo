<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PracticeAreaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PracticeArea extends Model
{
    /** @use HasFactory<PracticeAreaFactory> */
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * @return BelongsToMany<Lawyer, $this>
     */
    public function lawyers(): BelongsToMany
    {
        return $this->belongsToMany(Lawyer::class);
    }
}
