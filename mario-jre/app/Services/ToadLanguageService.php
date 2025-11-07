<?php

namespace App\Services;

class ToadLanguageService
{
    /**
     * Map des IDs de langue vers leurs noms
     */
    private const LANGUAGES = [
        1 => 'English',
        2 => 'Italian',
        3 => 'Japanese',
        4 => 'Mandarin',
        5 => 'French',
        6 => 'German'
    ];

    /**
     * Convertit un ID de langue en nom de langue
     */
    public function getLanguageName(?int $id): string
    {
        if ($id === null) {
            return 'Non spécifiée';
        }
        return self::LANGUAGES[$id] ?? 'Inconnue';
    }
}