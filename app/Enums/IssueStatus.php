<?php

namespace App\Enums;

enum IssueStatus: string
{
    case Open       = 'open';
    case InProgress = 'in_progress';
    case Closed     = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open       => 'Open',
            self::InProgress => 'In Progress',
            self::Closed     => 'Closed',
        };
    }

    /** Returns the Bootstrap badge variant (use as text-bg-{color()}). */
    public function color(): string
    {
        return match ($this) {
            self::Open       => 'primary',
            self::InProgress => 'warning',
            self::Closed     => 'success',
        };
    }
}
