<?php

namespace App\Services\Ressource;

use App\Enums\ResourceType;
use App\Models\Classe;
use App\Models\Serie;
use App\Models\Subject;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;
use ZipArchive;

class ResourceValidatorService
{
    private const MAX_FILE_SIZE = 10485760; // 10MB
    private const ALLOWED_MIME_TYPES = [
        'pdf' => 'application/pdf',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png'
    ];
    private const PROCESS_TIMEOUT = 120; // 2 minutes

    private array $cache = [];

    public function analyser(string $cheminFichier, string $extension, Classe $classe, Subject $subject, Serie $serie): array
    {
        $this->logStart(__METHOD__, [
            'file' => basename($cheminFichier),
            'extension' => $extension,
            'classe' => $classe->nom,
            'subject' => $subject->nom,
            'serie' => $serie->titre
        ]);

        try {
            // Validation initiale du fichier
            $validation = $this->validerFichier($cheminFichier, $extension);
            if (!$validation['valide']) {
                return $this->createErrorResponse($validation['message']);
            }

            // Extraction du texte
            $texte = $this->extraireTexte($cheminFichier, $extension);
            if (!$this->estTexteValide($texte)) {
                return $this->createErrorResponse('Le contenu extrait est insuffisant pour analyse');
            }

            // Analyse du contenu
            $typeRessource = $this->detecterTypeRessource($texte);
            $scoreContenu = $this->calculerScoreContenu($texte, $classe, $subject, $serie);
            $scoreMetadonnees = $this->verifierMetadonnees($classe, $subject, $serie);
            $scoreType = $this->evaluerTypeRessource($typeRessource, $texte);

            // Calcul du score final
            $scoreFinal = ($scoreContenu * 0.6) + ($scoreMetadonnees * 0.3) + ($scoreType * 0.1);
            $valide = $scoreFinal >= 0.6;

            $result = [
                'texte' => $texte,
                'type_ressource' => $typeRessource->value,
                'score' => $scoreFinal,
                'valide' => $valide,
                'commentaire' => $this->genererCommentaire($scoreFinal, $typeRessource)
            ];

            $this->logEnd(__METHOD__, ['score' => $scoreFinal, 'valid' => $valide]);
            return $result;
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'analyse", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->createErrorResponse("Une erreur technique est survenue lors de l'analyse");
        }
    }

    public function verifierDependances(): array
    {
        $dependances = [
            'pdftoppm' => 'poppler-utils',
            'tesseract' => 'tesseract-ocr',
            'convert' => 'imagemagick'
        ];

        $resultats = [];

        foreach ($dependances as $cmd => $package) {
            $process = new Process(['which', $cmd]);
            $process->run();
            $disponible = $process->isSuccessful();

            $resultats[] = [
                'commande' => $cmd,
                'package' => $package,
                'disponible' => $disponible,
                'message' => $disponible ? 'OK' : "Installer $package"
            ];
        }

        return $resultats;
    }

    private function createErrorResponse(string $message): array
    {
        return [
            'texte' => '',
            'type_ressource' => ResourceType::AUTRE->value,
            'score' => 0,
            'valide' => false,
            'commentaire' => $message
        ];
    }

    private function validerFichier(string $cheminFichier, string $extension): array
    {
        if (!file_exists($cheminFichier)) {
            return ['valide' => false, 'message' => 'Fichier introuvable'];
        }

        if (filesize($cheminFichier) > self::MAX_FILE_SIZE) {
            return ['valide' => false, 'message' => 'Fichier trop volumineux (max 10MB)'];
        }

        $mimeType = mime_content_type($cheminFichier);
        $expectedMime = self::ALLOWED_MIME_TYPES[strtolower($extension)] ?? null;

        if (!$expectedMime || $mimeType !== $expectedMime) {
            return ['valide' => false, 'message' => 'Type de fichier non supporté ou invalide'];
        }

        return ['valide' => true, 'message' => ''];
    }

    private function extraireTexte(string $cheminFichier, string $extension): string
    {
        $this->logStart(__METHOD__, ['extension' => $extension]);

        try {
            $extension = strtolower($extension);
            $method = match ($extension) {
                'pdf' => 'extraireTextePdf',
                'docx' => 'extraireTexteDocx',
                'jpg', 'jpeg', 'png' => 'extraireTexteImage',
                default => 'extraireTexteBrut'
            };

            $texte = $this->$method($cheminFichier);
            $texte = $this->nettoyerTexte($texte);

            $this->logEnd(__METHOD__, ['length' => strlen($texte)]);
            return $texte;
        } catch (\Exception $e) {
            Log::error("Erreur d'extraction", [
                'error' => $e->getMessage(),
                'file' => $cheminFichier
            ]);
            return '';
        }
    }

    private function extraireTextePdf(string $cheminPdf): string
    {
        $cacheKey = 'pdf_text_' . $this->getFileHash($cheminPdf);

        if (isset($this->cache[$cacheKey])) {
            Log::debug("Retour du texte depuis le cache");
            return $this->cache[$cacheKey];
        }

        // Essayer plusieurs méthodes avec fallback
        $methodOrder = [
            'extraireTextePdfStandard',
            'extraireViaPdfToPpm',
            'extraireViaConversionImage'
        ];

        $texte = '';
        foreach ($methodOrder as $method) {
            $texte = $this->$method($cheminPdf);
            $texte = $this->nettoyerTexte($texte);

            if ($this->estTexteValide($texte)) {
                Log::debug("Méthode réussie: $method");
                break;
            }
        }

        $this->cache[$cacheKey] = $texte;
        return $texte;
    }

    private function extraireTextePdfStandard(string $cheminPdf): string
    {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($cheminPdf);
            return trim($pdf->getText());
        } catch (\Exception $e) {
            Log::warning("Échec extraction PDF standard", ['error' => $e->getMessage()]);
            return '';
        }
    }

    private function extraireViaPdfToPpm(string $cheminPdf): string
    {
        $dossierTemp = sys_get_temp_dir();
        $prefixImage = tempnam($dossierTemp, 'pdfimg');

        try {
            // Convertir le PDF en images
            $this->runProcessWithTimeout([
                'pdftoppm',
                '-r',
                '300',
                '-png',
                $cheminPdf,
                $prefixImage
            ]);

            // OCR chaque image
            $texteComplet = '';
            $baseName = basename($prefixImage);
            $dir = dirname($prefixImage);
            $images = glob($dir . '/' . $baseName . '-*.png');

            foreach ($images as $image) {
                $texteComplet .= $this->runProcessWithTimeout([
                    'tesseract',
                    $image,
                    'stdout',
                    '-l',
                    'fra'
                ]) . "\n";
                unlink($image);
            }

            return trim($texteComplet);
        } finally {
            if (file_exists($prefixImage)) {
                unlink($prefixImage);
            }
        }
    }

    private function extraireViaConversionImage(string $cheminPdf): string
    {
        $imageTemp = tempnam(sys_get_temp_dir(), 'pdfocr') . '.png';

        try {
            // Convertir le PDF en image
            $this->runProcessWithTimeout([
                'convert',
                '-density',
                '300',
                $cheminPdf,
                '-background',
                'white',
                '-alpha',
                'remove',
                $imageTemp
            ]);

            // OCR sur l'image
            $texte = $this->runProcessWithTimeout([
                'tesseract',
                $imageTemp,
                'stdout',
                '-l',
                'fra'
            ]);

            return trim($texte);
        } finally {
            if (file_exists($imageTemp)) {
                unlink($imageTemp);
            }
        }
    }

    private function extraireTexteImage(string $cheminImage): string
    {
        try {
            return $this->runProcessWithTimeout([
                'tesseract',
                $cheminImage,
                'stdout',
                '-l',
                'fra'
            ]);
        } catch (\Exception $e) {
            Log::error("Erreur OCR", ['exception' => $e->getMessage()]);
            return '';
        }
    }

    private function extraireTexteDocx(string $cheminDocx): string
    {
        try {
            $zip = new ZipArchive;
            if ($zip->open($cheminDocx) !== true) {
                throw new \RuntimeException("Impossible d'ouvrir le fichier DOCX");
            }

            if (($index = $zip->locateName('word/document.xml')) === false) {
                $zip->close();
                throw new \RuntimeException("Structure DOCX invalide");
            }

            $content = $zip->getFromIndex($index);
            $zip->close();

            return strip_tags($content);
        } catch (\Exception $e) {
            Log::error("Erreur extraction DOCX", ['exception' => $e->getMessage()]);
            return '';
        }
    }

    private function extraireTexteBrut(string $cheminFichier): string
    {
        $content = file_get_contents($cheminFichier);
        return $content !== false ? $content : '';
    }

    private function detecterTypeRessource(string $texte): ResourceType
    {
        $texte = Str::lower($this->nettoyerTexte($texte));
        $wordCount = str_word_count($texte);

        if ($wordCount < 50) {
            return ResourceType::AUTRE;
        }

        $scores = array_fill_keys(array_column(ResourceType::cases(), 'value'), 0);

        $patterns = [
            ResourceType::COURS->value => [
                'introduction' => 3,
                'chapitre' => 3,
                'leçon' => 2,
                'notions' => 2,
                'explication' => 2,
                'contenu' => 1,
                'théorie' => 2,
                'document de cours' => 2,
                'programme' => 2,
                'plan de cours' => 2,
                'structure' => 1,
                'support de cours' => 2
            ],
            ResourceType::EXERCICE->value => [
                'exercice' => 3,
                'problème' => 3,
                'application' => 2,
                'énoncé' => 2,
                'série' => 2,
                'questions' => 1,
                'pratique' => 1,
                'tp' => 2,
                'td' => 2,
                'cas pratique' => 2,
                'activité' => 1
            ],
            ResourceType::EXAMEN->value => [
                'sujet' => 3,
                'examen' => 3,
                'épreuve' => 2,
                'test' => 2,
                'session' => 1,
                'baccalauréat' => 3,
                'bepc' => 3,
                'concours' => 3,
                'interro' => 2,
                'cc' => 2,
                'partiel' => 3,
                'dst' => 2,
                'évaluation' => 2
            ],
            ResourceType::CORRIGE->value => [
                'correction' => 3,
                'corrigé' => 3,
                'réponse' => 2,
                'solution' => 2,
                'résolution' => 2,
                'démonstration' => 2,
                'explication' => 1,
                'clé de réponse' => 2
            ],
            ResourceType::FICHE->value => [
                'résumé' => 3,
                'synthèse' => 2,
                'fiche' => 3,
                'bilan' => 2,
                'notes' => 1,
                'essentiel' => 2,
                'schéma récapitulatif' => 2
            ],
            ResourceType::MEMOIRE->value => [
                'mémoire' => 3,
                'rapport' => 3,
                'recherche' => 2,
                'soutenance' => 2,
                'étude' => 2,
                'problématique' => 2,
                'introduction générale' => 2,
                'conclusion' => 2,
                'bibliographie' => 2
            ],
            ResourceType::PRESENTATION->value => [
                'présentation' => 3,
                'diapositive' => 2,
                'powerpoint' => 2,
                'slide' => 2,
                'conférence' => 2,
                'exposé' => 2,
                'oral' => 2,
                'affiche' => 2,
                'document visuel' => 2
            ]
        ];

        foreach ($patterns as $type => $keywords) {
            foreach ($keywords as $mot => $poids) {
                $count = substr_count($texte, $mot);
                $scores[$type] += $count * $poids;
            }
            // Normalisation (échelle selon taille texte)
            $scores[$type] = $scores[$type] / max(1, $wordCount / 1000);
        }

        arsort($scores);
        $bestType = key($scores);

        return $scores[$bestType] >= 0.5 ? ResourceType::from($bestType) : ResourceType::AUTRE;
    }


    private function calculerScoreContenu(string $texte, Classe $classe, Subject $subject, Serie $serie): float
    {
        if (!$this->estTexteValide($texte)) {
            return 0;
        }

        $motsCles = array_merge(
            $classe->mots_cles_array ?? [],
            $subject->mots_cles_array ?? [],
            $serie->mots_cles_array ?? [],
            $this->getMotsClesGeneriques($subject)
        );

        $totalMots = count($motsCles);
        if ($totalMots === 0) {
            return 0.5;
        }

        $trouves = 0;
        $texteLower = Str::lower($texte);

        foreach ($motsCles as $mot) {
            if (Str::contains($texteLower, Str::lower($mot))) {
                $trouves++;
            }
        }

        $ratio = $trouves / $totalMots;
        $longueurScore = min(1, strlen($texte) / 5000);

        return ($ratio * 0.7) + ($longueurScore * 0.3);
    }

    private function getMotsClesGeneriques(Subject $subject): array
    {
        $matieres = [
            'mathématiques' => ['calcul', 'équation', 'fonction', 'géométrie', 'algèbre', 'trigonométrie'],
            'physique' => ['force', 'énergie', 'mouvement', 'onde', 'masse', 'vitesse'],
            'français' => ['lecture', 'grammaire', 'conjugaison', 'orthographe', 'vocabulaire'],
            'histoire' => ['date', 'événement', 'chronologie', 'empire', 'révolution'],
            'svt' => ['cellule', 'adn', 'évolution', 'écosystème']
        ];

        $nomMatiere = Str::lower($subject->nom);
        return $matieres[$nomMatiere] ?? [];
    }

    private function verifierMetadonnees(Classe $classe, Subject $subject, Serie $serie): float
    {
        $score = 0;

        // Vérification des objets associés
        $score += ($classe ? 0.3 : 0);
        $score += ($subject ? 0.3 : 0);
        $score += ($serie ? 0.2 : 0);

        // Vérification des mots-clés
        $score += (!empty($classe->mots_cles) ? 0.1 : 0);
        $score += (!empty($subject->mots_cles) ? 0.1 : 0);

        return min(1, $score);
    }

    private function evaluerTypeRessource(ResourceType $type, string $texte): float
    {
        $longueur = strlen($texte);

        return match ($type) {
            ResourceType::COURS => min(1, $longueur / 3000),
            ResourceType::EXERCICE => min(1, substr_count($texte, '?') / 5),
            ResourceType::FICHE => min(1, substr_count($texte, "\n") / 20),
            ResourceType::EXAMEN => min(1, substr_count($texte, 'points') / 3),
            default => 0.5
        };
    }

    private function genererCommentaire(float $score, ResourceType $type): string
    {
        return match (true) {
            $score >= 0.8 => "Ressource validée avec haute confiance (Type: {$type->value})",
            $score >= 0.6 => "Ressource validée (Type: {$type->value}), vérification manuelle recommandée",
            $score >= 0.4 => "Ressource douteuse (Score: " . round($score * 100) . "%, Type: {$type->value})",
            default => "Ressource non validée (Score: " . round($score * 100) . "%, Type: {$type->value})"
        };
    }

    private function runProcessWithTimeout(array $command, int $timeout = null): string
    {
        $process = new Process($command);
        $process->setTimeout($timeout ?? self::PROCESS_TIMEOUT);

        try {
            $process->mustRun();
            return $process->getOutput();
        } catch (ProcessTimedOutException $e) {
            Log::error("Timeout sur la commande", ['cmd' => implode(' ', $command)]);
            throw new \RuntimeException("Le traitement a pris trop de temps");
        } catch (ProcessFailedException $e) {
            Log::error("Échec de la commande", [
                'cmd' => implode(' ', $command),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function estTexteValide(string $texte): bool
    {
        $texte = trim($texte);
        if (empty($texte)) {
            return false;
        }

        $wordCount = str_word_count($texte);
        $uniqueWords = count(array_unique(explode(' ', $texte)));

        return $wordCount > 50 && $uniqueWords > 20;
    }

    private function nettoyerTexte(string $texte): string
    {
        $texte = preg_replace('/[^\PC\s]/u', '', $texte);
        $texte = preg_replace('/\s+/', ' ', $texte);
        return trim(mb_convert_encoding($texte, 'UTF-8', 'UTF-8'));
    }

    private function getFileHash(string $cheminFichier): string
    {
        return md5_file($cheminFichier) . '_' . filesize($cheminFichier);
    }

    private function logStart(string $method, array $context = []): void
    {
        Log::info("Début: $method", $context + [
            'memory' => memory_get_usage(),
            'pid' => getmypid()
        ]);
    }

    private function logEnd(string $method, array $context = []): void
    {
        Log::info("Fin: $method", $context + [
            'memory' => memory_get_usage(),
            'duration' => microtime(true) - LARAVEL_START
        ]);
    }
}
