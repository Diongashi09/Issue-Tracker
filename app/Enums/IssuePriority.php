<?php

namespace App\Enums;

enum IssuePriority: string
{
    case Low    = 'low';
    case Medium = 'medium';
    case High   = 'high';

    public function label(): string
    {
        return match ($this) {
            self::Low    => 'Low',
            self::Medium => 'Medium',
            self::High   => 'High',
        };
    }

    /** Returns the Bootstrap badge variant (use as text-bg-{color()}). */
    public function color(): string
    {
        return match ($this) {
            self::Low    => 'secondary',
            self::Medium => 'warning',
            self::High   => 'danger',
        };
    }
}
