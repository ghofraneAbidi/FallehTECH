<?php

namespace App\Enum;

enum StatutCandidature: string
{
    case EN_ATTENTE = 'en_attente';  // Waiting for approval
    case ACCEPTEE = 'acceptee';      // Accepted
    case REFUSEE = 'refusee';        // Rejected

    /**
     * Get a human-readable label for the enum values.
     */
    public function label(): string
    {
        return match($this) {
            self::EN_ATTENTE => 'En attente',
            self::ACCEPTEE => 'Acceptée',
            self::REFUSEE => 'Refusée',
        };
    }
}
