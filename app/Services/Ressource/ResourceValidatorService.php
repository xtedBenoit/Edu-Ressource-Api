<?php

namespace App\Services\Ressource;

use App\Enums\ResourceType;
use App\Models\Classe;
use App\Models\Serie;
use App\Models\Subject;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser;
use Symfony\Component\Process\Process;

class ResourceValidatorService
{
    public function analyser(string $cheminFichier, string $extension, Classe $classe, Subject $subject, Serie $serie): array
    {
        Log::info("Début de l'analyse de la ressource", [
            'fichier' => $cheminFichier,
            'extension' => $extension,
            'classe' => $classe->nom,
            'matiere' => $subject->nom,
            'serie' => $serie->titre
        ]);

        $texte = $this->extraireTexte($cheminFichier, $extension);
        Log::debug("Texte extrait (extrait)", ['debut_texte' => substr($texte, 0, 200)]);

        $typeRessource = $this->detecterTypeRessource($texte);
        Log::info("Type de ressource détecté", ['type' => $typeRessource->value]);

        $scoreContenu = $this->calculerScoreContenu($texte, $classe, $subject, $serie);
        $scoreMetadonnees = $this->verifierMetadonnees($classe, $subject, $serie);
        $scoreType = $this->evaluerTypeRessource($typeRessource, $texte);

        Log::debug("Scores intermédiaires", [
            'contenu' => $scoreContenu,
            'metadonnees' => $scoreMetadonnees,
            'type' => $scoreType
        ]);

        $scoreFinal = ($scoreContenu * 0.6) + ($scoreMetadonnees * 0.3) + ($scoreType * 0.1);
        $valide = $scoreFinal >= 0.6;

        Log::info("Résultat final de la validation", [
            'score_final' => $scoreFinal,
            'valide' => $valide
        ]);

        return [
            'texte' => $texte,
            'type_ressource' => $typeRessource->value, // On utilise ->value pour l'enum
            'score' => $scoreFinal,
            'valide' => $valide,
            'commentaire' => $this->genererCommentaire($scoreFinal, $typeRessource)
        ];
    }

    private function extraireTexte(string $cheminFichier, string $extension): string
    {
        Log::debug("Tentative d'extraction de texte", ['extension' => $extension]);

        try {
            if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png'])) {
                Log::debug("Extraction depuis image");
                return $this->extraireTexteImage($cheminFichier);
            }

            if (strtolower($extension) === 'pdf') {
                Log::debug("Extraction depuis PDF");
                return $this->extraireTextePdf($cheminFichier);
            }

            if (in_array(strtolower($extension), ['docx'])) {
                Log::debug("Extraction depuis DOCX");
                return $this->extraireTexteDocx($cheminFichier);
            }

            $content = file_get_contents($cheminFichier) ?: '';
            Log::debug("Extraction directe", ['longueur' => strlen($content)]);
            return $content;
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
        // 1. Essayer d'abord l'extraction texte standard (pour PDF textuels)
        $texte = $this->extraireTextePdfStandard($cheminPdf);
        if (!empty(trim($texte))) {
            Log::debug("Texte extrait via PDF standard");
            return $texte;
        }

        // 2. Si échec, essayer l'extraction OCR (pour PDF scannés/images)
        Log::debug("Aucun texte trouvé via extraction standard, tentative OCR...");
        return $this->extraireTextePdfOcr($cheminPdf);
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

    private function extraireTextePdfOcr(string $cheminPdf): string
    {
        try {
            // Solution 1: Utiliser pdftoppm + tesseract (meilleure qualité)
            $texte = $this->extraireViaPdfToPpm($cheminPdf);
            if (!empty(trim($texte))) {
                return $texte;
            }

            // Solution 2: Fallback - convertir en image puis OCR
            return $this->extraireViaConversionImage($cheminPdf);
        } catch (\Exception $e) {
            Log::error("Échec OCR PDF", ['error' => $e->getMessage()]);
            return '';
        }
    }

    private function extraireViaPdfToPpm(string $cheminPdf): string
    {
        // Nécessite poppler-utils et tesseract installés
        $dossierTemp = sys_get_temp_dir();
        $prefixImage = tempnam($dossierTemp, 'pdfimg');

        try {
            // Étape 1: Convertir le PDF en images (une par page)
            $process = new Process([
                'pdftoppm',
                '-r',
                '300', // Résolution DPI
                '-png',
                $cheminPdf,
                $prefixImage
            ]);
            $process->mustRun();

            // Étape 2: OCR chaque image générée
            $texteComplet = '';
            $images = glob($prefixImage . '*.png');

            foreach ($images as $image) {
                $process = new Process([
                    'tesseract',
                    $image,
                    'stdout',
                    '-l',
                    'fra'
                ]);
                $process->mustRun();
                $texteComplet .= $process->getOutput() . "\n";
                unlink($image); // Nettoyage
            }

            return trim($texteComplet);
        } finally {
            // Nettoyage des fichiers temporaires
            if (file_exists($prefixImage)) {
                unlink($prefixImage);
            }
        }
    }

    private function extraireViaConversionImage(string $cheminPdf): string
    {
        // Solution de fallback plus simple mais moins précise
        try {
            // Convertir le PDF en une seule image
            $imageTemp = tempnam(sys_get_temp_dir(), 'pdfocr') . '.png';

            $process = new Process([
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
            $process->mustRun();

            // OCR sur l'image résultante
            $process = new Process([
                'tesseract',
                $imageTemp,
                'stdout',
                '-l',
                'fra'
            ]);
            $process->mustRun();
            $texte = $process->getOutput();

            unlink($imageTemp);
            return trim($texte);
        } catch (\Exception $e) {
            if (isset($imageTemp) && file_exists($imageTemp)) {
                unlink($imageTemp);
            }
            Log::error("Échec conversion PDF en image", ['error' => $e->getMessage()]);
            return '';
        }
    }

    private function extraireTexteImage(string $cheminImage): string
    {
        try {
            $process = new Process(['tesseract', $cheminImage, 'stdout', '-l', 'fra']);
            $process->run();

            if (!$process->isSuccessful()) {
                Log::error("Échec OCR", ['error' => $process->getErrorOutput()]);
                return '';
            }

            $output = $process->getOutput();
            Log::debug("Texte image extrait", ['longueur' => strlen($output)]);
            return $output;
        } catch (\Exception $e) {
            Log::error("Erreur OCR", ['exception' => $e->getMessage()]);
            return '';
        }
    }

    private function extraireTexteDocx(string $cheminDocx): string
    {
        try {
            $zip = new \ZipArchive;
            if ($zip->open($cheminDocx) !== true) {
                Log::error("Impossible d'ouvrir le DOCX");
                return '';
            }

            if (($index = $zip->locateName('word/document.xml')) === false) {
                Log::error("Fichier document.xml introuvable dans le DOCX");
                $zip->close();
                return '';
            }

            $content = $zip->getFromIndex($index);
            $zip->close();

            $text = strip_tags($content);
            Log::debug("Texte DOCX extrait", ['longueur' => strlen($text)]);
            return $text;
        } catch (\Exception $e) {
            Log::error("Erreur extraction DOCX", ['exception' => $e->getMessage()]);
            return '';
        }
    }

    private function detecterTypeRessource(string $texte): ResourceType
    {
        $texte = Str::lower($texte);

        $patterns = [
            ResourceType::EXERCICE->value => ['exercice', 'problème', 'question', 'énoncé'],
            ResourceType::CORRIGE->value => ['corrigé', 'solution', 'réponse'],
            ResourceType::EXAMEN->value => ['examen', 'contrôle', 'épreuve', 'bac', 'brevet'],
            ResourceType::FICHE->value => ['fiche', 'résumé', 'synthèse', 'mémo'],
            ResourceType::COURS->value => ['chapitre', 'leçon', 'définition', 'théorème'],
        ];

        foreach ($patterns as $typeValue => $mots) {
            foreach ($mots as $mot) {
                if (Str::contains($texte, $mot)) {
                    Log::debug("Type détecté via mot-clé", ['mot' => $mot, 'type' => $typeValue]);
                    return ResourceType::from($typeValue);
                }
            }
        }

        Log::debug("Aucun type spécifique détecté, retour par défaut");
        return ResourceType::AUTRE;
    }

    private function calculerScoreContenu(string $texte, Classe $classe, Subject $subject, Serie $serie): float
    {
        if (empty($texte)) {
            Log::debug("Score contenu: texte vide");
            return 0;
        }

        // Mots-clés spécifiques à la matière
        $motsCles = array_merge(
            $classe->mots_cles_array ?? [],
            $subject->mots_cles_array ?? [],
            $serie->mots_cles_array ?? [],
            $this->getMotsClesGeneriques($subject)
        );

        Log::debug("Mots-clés utilisés", ['mots_cles' => $motsCles]);

        $totalMots = count($motsCles);
        if ($totalMots === 0) {
            Log::debug("Score contenu: aucun mot-clé disponible");
            return 0.5;
        }

        $trouves = 0;
        foreach ($motsCles as $mot) {
            if (Str::contains(Str::lower($texte), Str::lower($mot))) {
                $trouves++;
                Log::debug("Mot-clé trouvé", ['mot' => $mot]);
            }
        }

        // Ajustement pour les petits fichiers
        $ratio = $trouves / $totalMots;
        $longueurScore = min(1, strlen($texte) / 5000); // Normalisé pour 5000 caractères

        $score = ($ratio * 0.7) + ($longueurScore * 0.3);
        Log::debug("Score contenu calculé", [
            'ratio' => $ratio,
            'longueur_score' => $longueurScore,
            'score_final' => $score
        ]);

        return $score;
    }

    private function getMotsClesGeneriques(Subject $subject): array
    {
        $matieres = [
            'mathématiques' => ['calcul', 'équation', 'fonction', 'géométrie'],
            'physique' => ['force', 'énergie', 'mouvement', 'onde'],
            'français' => ['lecture', 'grammaire', 'conjugaison', 'orthographe'],
        ];

        $nomMatiere = Str::lower($subject->nom);
        $mots = $matieres[$nomMatiere] ?? [];

        Log::debug("Mots-clés génériques pour matière", [
            'matiere' => $nomMatiere,
            'mots' => $mots
        ]);

        return $mots;
    }

    private function verifierMetadonnees(Classe $classe, Subject $subject, Serie $serie): float
    {
        $score = 0;

        if ($classe) $score += 0.3;
        if ($subject) $score += 0.3;
        if ($serie) $score += 0.2;

        // Vérification des mots-clés
        if (!empty($classe->mots_cles)) $score += 0.1;
        if (!empty($subject->mots_cles)) $score += 0.1;

        $finalScore = min(1, $score);
        Log::debug("Score métadonnées", ['score' => $finalScore]);

        return $finalScore;
    }

    private function evaluerTypeRessource(ResourceType $type, string $texte): float
    {
        $longueur = strlen($texte);

        $score = match ($type) {
            ResourceType::COURS => min(1, $longueur / 3000),
            ResourceType::EXERCICE => min(1, substr_count($texte, '?') / 5),
            ResourceType::FICHE => min(1, substr_count($texte, "\n") / 20),
            ResourceType::EXAMEN => min(1, substr_count($texte, 'points') / 3),
            default => 0.5
        };

        Log::debug("Score type ressource", [
            'type' => $type->value,
            'score' => $score
        ]);

        return $score;
    }

    private function genererCommentaire(float $score, ResourceType $type): string
    {
        $comment = match (true) {
            $score >= 0.8 => "Ressource validée automatiquement avec haute confiance (Type: {$type->label()})",
            $score >= 0.6 => "Ressource validée (Type: {$type->label()}), vérification manuelle recommandée",
            default => "Ressource non validée (Score: " . round($score * 100) . "%, Type: {$type->label()})"
        };

        Log::debug("Commentaire de validation généré", ['commentaire' => $comment]);
        return $comment;
    }
}
