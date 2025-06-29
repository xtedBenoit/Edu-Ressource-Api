<?php

namespace App\Enums;

enum ResourceType: string
{
    case COURS = 'cours';
    case EXERCICE = 'exercice';
    case FICHE = 'fiche';
    case EXAMEN = 'examen';
    case CORRIGE = 'corrige';
    case VIDEO = 'video';
    case MEMOIRE = 'memoire';
    case PRESENTATION = 'presentation';
    case AUTRE = 'autre';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
    
    public function label(): string
    {
        return match($this) {
            self::COURS => 'Cours',
            self::EXERCICE => 'Exercice',
            self::FICHE => 'Fiche de révision',
            self::EXAMEN => 'Examen',
            self::CORRIGE => 'Corrigé',
            self::VIDEO => 'Vidéo pédagogique',
            self::MEMOIRE => 'Mémoire ou rapport',
            self::PRESENTATION => 'Présentation / Slide',
            self::AUTRE => 'Autre type',
        };
    }
}
