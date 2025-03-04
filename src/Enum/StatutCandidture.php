<?php

namespace App\Enum;

enum StatutCandidature: string
{
    case EN_ATTENTE = 'en_attente';   // Waiting for approval
    case ACCEPTEE = 'acceptee';       // Accepted
    case REFUSEE = 'refusee';         // Rejected
    case TERMINEE = 'terminee';       // Worker marked as completed
    case CONFIRMEE = 'confirmee';     // Farmer confirmed completion

    /**
     * Get a human-readable label for the enum values.
     */
    public function label(): string
    {
        return match($this) {
            self::EN_ATTENTE => 'En attente',
            self::ACCEPTEE => 'Acceptée',
            self::REFUSEE => 'Refusée',
            self::TERMINEE => 'Terminée par le travailleur',
            self::CONFIRMEE => 'Confirmée par le fermier',
        };
    }
}
