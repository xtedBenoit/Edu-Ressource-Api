<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Classe;
use App\Models\Serie;
use App\Models\Subject;

class EducationSeeder extends Seeder
{
    public function run()
    {
        // Création des matières avec mots clés étendus
        $subjects = [
            ['nom' => 'Français', 'code' => 'FR', 'mots_cles' => ['français', 'langue', 'grammaire', 'orthographe', 'lecture', 'écriture', 'littérature']],
            ['nom' => 'Mathématiques', 'code' => 'MATH', 'mots_cles' => ['mathématiques', 'algèbre', 'géométrie', 'calcul', 'arithmétique', 'statistiques', 'analyse']],
            ['nom' => 'Physique-Chimie', 'code' => 'PC', 'mots_cles' => ['physique', 'chimie', 'science', 'électricité', 'mécanique', 'réactions', 'éléments', 'matière']],
            ['nom' => 'Sciences de la Vie et de la Terre', 'code' => 'SVT', 'mots_cles' => ['svt', 'biologie', 'écologie', 'géologie', 'terre', 'vie', 'écosystème']],
            ['nom' => 'Histoire-Géographie', 'code' => 'HG', 'mots_cles' => ['histoire', 'géographie', 'monde', 'civilisation', 'cartographie', 'événements', 'pays']],
            ['nom' => 'Anglais', 'code' => 'ANG', 'mots_cles' => ['anglais', 'langue', 'vocabulaire', 'grammaire', 'conversation', 'lecture']],
            ['nom' => 'Philosophie', 'code' => 'PHILO', 'mots_cles' => ['philosophie', 'pensée', 'ethique', 'morale', 'logique', 'raisonnement']],
            ['nom' => 'Éducation Civique et Morale', 'code' => 'ECM', 'mots_cles' => ['éducation civique', 'morale', 'citoyenneté', 'droits', 'devoirs', 'lois']],
            ['nom' => 'Technologie', 'code' => 'TECH', 'mots_cles' => ['technologie', 'informatique', 'machines', 'ingénierie', 'innovation', 'robotique']],
            ['nom' => 'Informatique', 'code' => 'INFO', 'mots_cles' => ['informatique', 'programmation', 'algorithmique', 'ordinateur', 'logiciel', 'hardware']],
            ['nom' => 'Éducation Physique et Sportive', 'code' => 'EPS', 'mots_cles' => ['eps', 'sport', 'activité physique', 'santé', 'exercice', 'fitness']],
            ['nom' => 'Allemand', 'code' => 'ALL', 'mots_cles' => ['allemand', 'langue', 'vocabulaire', 'grammaire', 'conversation']],
            ['nom' => 'Espagnol', 'code' => 'ESP', 'mots_cles' => ['espagnol', 'langue', 'vocabulaire', 'grammaire', 'conversation']],
            ['nom' => 'Sciences Économiques et Sociales', 'code' => 'SES', 'mots_cles' => ['économie', 'sociologie', 'marché', 'entreprise', 'finance', 'sociaux']],
            ['nom' => 'Comptabilité', 'code' => 'COMPTA', 'mots_cles' => ['comptabilité', 'comptes', 'finances', 'bilan', 'gestion', 'comptable']],
            ['nom' => 'Gestion', 'code' => 'GEST', 'mots_cles' => ['gestion', 'management', 'entreprise', 'organisation', 'ressources']],
            ['nom' => 'Droit', 'code' => 'DRT', 'mots_cles' => ['droit', 'loi', 'justice', 'règlementation', 'législation']],
            ['nom' => 'Littérature', 'code' => 'LITT', 'mots_cles' => ['littérature', 'œuvres', 'auteurs', 'lecture', 'analyse']],
        ];

        foreach ($subjects as $subjectData) {
            Subject::create([
                'nom' => $subjectData['nom'],
                'code' => $subjectData['code'],
                'mots_cles' => $subjectData['mots_cles'],
            ]);
        }

        // Création des classes avec mots clés étendus
        $classes = [
            ['nom' => 'CP1', 'mots_cles' => ['cp1', 'cours préparatoire', 'primaire', 'début école']],
            ['nom' => 'CP2', 'mots_cles' => ['cp2', 'cours préparatoire', 'primaire', 'début école']],
            ['nom' => 'CE1', 'mots_cles' => ['ce1', 'cours élémentaire', 'primaire']],
            ['nom' => 'CE2', 'mots_cles' => ['ce2', 'cours élémentaire', 'primaire']],
            ['nom' => 'CM1', 'mots_cles' => ['cm1', 'cours moyen', 'primaire']],
            ['nom' => 'CM2', 'mots_cles' => ['cm2', 'cours moyen', 'primaire']],
            ['nom' => '6e', 'mots_cles' => ['sixième', 'collège', 'début collège']],
            ['nom' => '5e', 'mots_cles' => ['cinquième', 'collège']],
            ['nom' => '4e', 'mots_cles' => ['quatrième', 'collège']],
            ['nom' => '3e', 'mots_cles' => ['troisième', 'collège', 'brevet']],
            ['nom' => 'Seconde', 'mots_cles' => ['seconde', 'lycée', 'début lycée']],
            ['nom' => 'Première', 'mots_cles' => ['première', 'lycée']],
            ['nom' => 'Terminale', 'mots_cles' => ['terminale', 'lycée', 'bac']],
        ];

        foreach ($classes as $classeData) {
            Classe::create([
                'nom' => $classeData['nom'],
                'niveau' => $classeData['nom'],
                'subject_ids' => [], // À remplir selon les matières associées
                'mots_cles' => $classeData['mots_cles'],
            ]);
        }

        // Création des séries avec mots clés étendus
        $series = [
            ['titre' => 'D', 'description' => 'Série scientifique D', 'mots_cles' => ['d', 'scientifique', 'maths', 'physique', 'chimie', 'svt']],
            ['titre' => 'C', 'description' => 'Série scientifique C', 'mots_cles' => ['c', 'scientifique', 'maths', 'physique', 'chimie']],
            ['titre' => 'A4', 'description' => 'Série littéraire A4', 'mots_cles' => ['a4', 'littéraire', 'français', 'philosophie', 'histoire', 'lettres']],
            ['titre' => 'F', 'description' => 'Série technique F', 'mots_cles' => ['f', 'technique', 'informatique', 'technologie', 'gestion']],
            ['titre' => 'G1', 'description' => 'Série technique G1', 'mots_cles' => ['g1', 'technique', 'gestion', 'comptabilité', 'économie']],
            ['titre' => 'G2', 'description' => 'Série technique G2', 'mots_cles' => ['g2', 'technique', 'gestion', 'droit', 'économie']],
        ];

        foreach ($series as $serieData) {
            Serie::create([
                'titre' => $serieData['titre'],
                'description' => $serieData['description'],
                'mots_cles' => $serieData['mots_cles'],
            ]);
        }
    }
}
