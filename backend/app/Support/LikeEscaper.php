<?php

declare(strict_types=1);

namespace App\Support;

final class LikeEscaper
{
    /**
     * LIKE 句のワイルドカード(% と _)とエスケープ文字(\)を文字として扱えるようにする。
     * MySQL の LIKE は既定でバックスラッシュをエスケープ文字とする。
     */
    public static function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
