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
            [
                'nom' => 'Français',
                'code' => 'FR',
                'mots_cles' => [
                    'français',
                    'langue française',
                    'grammaire',
                    'orthographe',
                    'conjugaison',
                    'expression écrite',
                    'analyse de texte',
                    'compréhension écrite',
                    'lecture cursive',
                    'rédaction',
                    'type de texte',
                    'texte narratif',
                    'texte argumentatif',
                    'poésie',
                    'roman',
                    'nouvelle',
                    'théâtre',
                    'fable',
                    'figure de style',
                    'comparaison',
                    'métaphore',
                    'champ lexical',
                    'analyse grammaticale',
                    'classes grammaticales',
                    'fonctions grammaticales',
                    'subordonnée',
                    'complément circonstanciel',
                    'proposition',
                    'discours direct',
                    'discours indirect',
                    'vocabulaire',
                    'étymologie',
                    'sujet de dissertation',
                    'résumé de texte',
                    'plan dialectique',
                    'analyse linéaire',
                    'lecture analytique',
                    'orthographe lexicale',
                    'orthographe grammaticale',
                    'synthèse de documents',
                    'lecture comparée',
                    'analyse thématique'
                ]
            ],
            [
                'nom' => 'Mathématiques',
                'code' => 'MATH',
                'mots_cles' => [
                    'mathématiques',
                    'algèbre',
                    'géométrie',
                    'arithmétique',
                    'statistiques',
                    'probabilités',
                    'équations',
                    'fonctions',
                    'droites',
                    'paraboles',
                    'intégrales',
                    'dérivées',
                    'limites',
                    'vecteurs',
                    'repérage',
                    'coordonnées',
                    'calcul littéral',
                    'fractions',
                    'nombres relatifs',
                    'pourcentages',
                    'produit en croix',
                    'théorème de Pythagore',
                    'théorème de Thalès',
                    'triangle rectangle',
                    'aires',
                    'volumes',
                    'solides',
                    'cercles',
                    'angles',
                    'médiane',
                    'mode',
                    'moyenne',
                    'écart-type',
                    'distribution',
                    'tableau de variation',
                    'intervalles',
                    'représentation graphique',
                    'suite numérique',
                    'arithmétique modulaire',
                    'résolution de problème',
                    'inéquation',
                    'calcul mental',
                    'géométrie dans l’espace',
                    'exercice à étapes',
                    'démonstration',
                    'justification',
                    'construction géométrique',
                    'question ouverte',
                    'résolution par méthode'
                ]
            ],
            [
                'nom' => 'Physique-Chimie',
                'code' => 'PC',
                'mots_cles' => [
                    'physique',
                    'chimie',
                    'mécanique',
                    'optique',
                    'électricité',
                    'circuits',
                    'tension',
                    'intensité',
                    'résistance',
                    'loi d’Ohm',
                    'énergie cinétique',
                    'énergie potentielle',
                    'puissance',
                    'travail',
                    'masse',
                    'force',
                    'poids',
                    'vitesse',
                    'accélération',
                    'réaction chimique',
                    'équation chimique',
                    'molécule',
                    'atome',
                    'éléments chimiques',
                    'tableau périodique',
                    'liaison chimique',
                    'solution aqueuse',
                    'acide',
                    'base',
                    'pH',
                    'précipité',
                    'expérience',
                    'protocole',
                    'mesure',
                    'balance',
                    'verrerie',
                    'dilution',
                    'concentration',
                    'dosage',
                    'spectre lumineux',
                    'ondes',
                    'pression',
                    'température',
                    'changement d’état',
                    'fusion',
                    'vaporisation',
                    'formule chimique',
                    'modèle atomique',
                    'valence',
                    'constante',
                    'radioactivité',
                    'calcul d’incertitude',
                    'exercice expérimental',
                    'exercice de calcul',
                    'exercice de synthèse'
                ]
            ],
            [
                'nom' => 'Sciences de la Vie et de la Terre',
                'code' => 'SVT',
                'mots_cles' => [
                    'svt',
                    'biologie',
                    'géologie',
                    'cellule',
                    'mitose',
                    'méiose',
                    'adn',
                    'gène',
                    'mutation',
                    'reproduction',
                    'système nerveux',
                    'système digestif',
                    'respiration cellulaire',
                    'photosynthèse',
                    'écosystème',
                    'biodiversité',
                    'cycle de vie',
                    'cycle de l’eau',
                    'plaque tectonique',
                    'volcan',
                    'séisme',
                    'strates',
                    'roches',
                    'fossiles',
                    'érosion',
                    'climat',
                    'pédologie',
                    'milieu naturel',
                    'observation microscopique',
                    'schéma fonctionnel',
                    'document scientifique',
                    'exercice de restitution',
                    'exercice de comparaison',
                    'expérience',
                    'interprétation de graphique',
                    'analyse de coupe géologique',
                    'anatomie',
                    'génétique',
                    'évolution'
                ]
            ],
            [
                'nom' => 'Histoire-Géographie',
                'code' => 'HG',
                'mots_cles' => [
                    'histoire',
                    'géographie',
                    'civilisation',
                    'révolution française',
                    'première guerre mondiale',
                    'deuxième guerre mondiale',
                    'guerre froide',
                    'colonisation',
                    'décolonisation',
                    'grandes découvertes',
                    'monarchie',
                    'république',
                    'totalitarisme',
                    'démocratie',
                    'carte',
                    'territoire',
                    'population',
                    'flux migratoires',
                    'urbanisation',
                    'mondialisation',
                    'géopolitique',
                    'pays développés',
                    'pays émergents',
                    'développement durable',
                    'aléas naturels',
                    'risques',
                    'relief',
                    'climat',
                    'exercice de carte',
                    'frise chronologique',
                    'analyse de document historique',
                    'croquis',
                    'schéma géographique',
                    'question problématisée',
                    'analyse de texte historique'
                ]
            ],
            ['nom' => 'Anglais', 'code' => 'ANG', 'mots_cles' => ['anglais', 'langue', 'vocabulaire', 'grammaire', 'conversation', 'lecture']],
            [
                'nom' => 'Philosophie',
                'code' => 'PHILO',
                'mots_cles' => [
                    'philosophie','pensée','ethique',
                    'morale',
                    'logique',
                    'raisonnement',
                    'existence',
                    'liberté',
                    'justice',
                    'vérité',
                    'conscience',
                    'inconscient',
                    'raison',
                    'bonheur',
                    'société',
                    'droit',
                    'devoir',
                    'politique',
                    'langage',
                    'art',
                    'travail',
                    'science',
                    'religion',
                    'nature humaine',
                    'sujet de dissertation',
                    'argument',
                    'exemple',
                    'analyse philosophique'
                ]
            ],
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

        $subjectMap = [];
        foreach ($subjects as $subjectData) {
            $subject = Subject::create([
                'nom' => $subjectData['nom'],
                'code' => $subjectData['code'],
                'mots_cles' => $subjectData['mots_cles'],
            ]);
            $subjectMap[$subject->code] = $subject->_id;
        }

        // Liste des matières générales valables pour tout le monde
        $commonSubjects = [
            'FR',
            'MATH',
            'PC',
            'SVT',
            'HG',
            'ANG',
            'ECM',
            'EPS'
        ];

        // Ajout progressif de la philosophie au lycée
        $lyceeGeneralSubjects = array_merge($commonSubjects, ['PHILO']);

        // Création des classes et attribution de matières
        $classeMap = [];
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
            $subjectsForClasse = match ($classeData['nom']) {
                'CP1', 'CP2', 'CE1', 'CE2', 'CM1', 'CM2' => ['FR', 'MATH', 'HG', 'EPS'],
                '6e', '5e', '4e', '3e' => $commonSubjects,
                'Seconde' => $commonSubjects, // La série commence ici mais les matières restent communes
                'Première', 'Terminale' => $lyceeGeneralSubjects,
                default => $commonSubjects
            };

            $subjectIds = array_values(array_intersect_key($subjectMap, array_flip($subjectsForClasse)));

            $classe = Classe::create([
                'nom' => $classeData['nom'],
                'niveau' => $classeData['nom'],
                'subject_ids' => $subjectIds,
                'mots_cles' => $classeData['mots_cles'],
            ]);

            $classeMap[$classe->nom] = $classe->_id;
        }

        // Création des séries avec mots clés étendus
        $series = [
            [
                'titre' => 'Serie D',
                'description' => 'Série scientifique D',
                'mots_cles' => ['serie : d', 'scientifique', 'maths', 'physique', 'chimie', 'svt'],
                'classes' => ['Première', 'Terminale'],
                'subjects' => ['MATH', 'PC', 'SVT', 'PHILO', 'FR', 'HG', 'ANG']
            ],
            [
                'titre' => 'Serie C',
                'description' => 'Série scientifique C',
                'mots_cles' => ['serie : d', 'scientifique', 'maths', 'physique', 'chimie'],
                'classes' => ['Première', 'Terminale'],
                'subjects' => ['MATH', 'PC', 'PHILO', 'FR', 'HG', 'ANG']
            ],
            [
                'titre' => 'Serie A4',
                'description' => 'Série littéraire A4',
                'mots_cles' => ['serie : a4', 'littéraire', 'français', 'philosophie', 'histoire', 'lettres'],
                'classes' => ['Première', 'Terminale'],
                'subjects' => ['FR', 'PHILO', 'LITT', 'HG', 'ANG']
            ],
            [
                'titre' => 'Serie F',
                'description' => 'Série technique F',
                'mots_cles' => ['serie : f', 'technique', 'technologie', 'informatique', 'gestion'],
                'classes' => ['Première', 'Terminale'],
                'subjects' => ['TECH', 'INFO', 'GEST', 'PHILO', 'FR', 'MATH']
            ],
            [
                'titre' => 'Serie G1',
                'description' => 'Série technique G1',
                'mots_cles' => ['serie : g1', 'technique', 'gestion', 'comptabilité', 'économie'],
                'classes' => ['Première', 'Terminale'],
                'subjects' => ['GEST', 'COMPTA', 'SES', 'FR', 'PHILO']
            ],
            [
                'titre' => 'Serie G2',
                'description' => 'Série technique G2',
                'mots_cles' => ['serie : g2', 'technique', 'droit', 'économie', 'gestion'],
                'classes' => ['Première', 'Terminale'],
                'subjects' => ['GEST', 'COMPTA', 'DRT', 'SES', 'FR', 'PHILO']
            ],
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
