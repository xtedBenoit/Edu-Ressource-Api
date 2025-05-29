<?php

namespace App\Http\Controllers\Api\Resource;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\Resource;
use App\Models\Serie;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser;

class ResourceController extends Controller
{
    /**
     * Afficher toutes les ressources validées avec filtres.
     */
    /**
     * Afficher toutes les ressources validées avec filtres et pagination.
     */
    public function index(Request $request)
    {
        $query = Resource::query()->where('valide', true);

        if ($request->filled('classe_id')) {
            $query->where('classe_id', $request->classe_id);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('serie_id')) {
            $query->where('serie_id', $request->serie_id);
        }

        if ($request->filled('mots_cles')) {
            $motsCles = explode(',', $request->mots_cles);
            $query->whereIn('mots_cles', $motsCles);
        }

        $perPage = $request->input('per_page', 10);
        $resources = $query->latest()->paginate($perPage);

        return ApiResponse::success([
            'data' => $resources->items(),
            'pagination' => [
                'current_page' => $resources->currentPage(),
                'last_page' => $resources->lastPage(),
                'per_page' => $resources->perPage(),
                'total' => $resources->total(),
            ],
        ]);
    }

    /**
     * Ajouter une nouvelle ressource.
     */
    public function store(Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:pdf,jpg,jpeg,png,video',
            'fichier' => 'nullable|file|mimes:pdf,jpg,jpeg,png,mp4|max:20480',
            'lien' => 'nullable|url',
            'classe_id' => 'required|string|exists:classes,id',
            'subject_id' => 'required|string|exists:subjects,id',
            'serie_id' => 'required|string|exists:series,id',
        ]);

        $cheminFichier = null;
        $fichierHash = null;


        if ($request->hasFile('fichier')) {
            $fichier = $request->file('fichier');

            $md5 = md5_file($fichier->getRealPath());
            $sha1 = sha1_file($fichier->getRealPath());
            $fichierHash = $md5 . '_' . $sha1;

            // Vérifie si ce fichier existe déjà (hash combiné)
            if (Resource::where('fichier_hash', $fichierHash)->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ce fichier a déjà été publié.',
                ], 409);
            }
            $cheminFichier = $fichier->store('ressources', 'public');

            // Extraction du contenu et validation automatique
            $motsCles = $this->motsClesFusionnes($request->classe_id, $request->serie_id, $request->subject_id);
            $contenuTexte = $this->extractTextFromFile($fichier->getRealPath(), $request->type);
            $pourcentage = $this->pourcentageMotsTrouves($contenuTexte, $motsCles);

            if ($pourcentage < 10) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Le fichier ne contient pas assez de mots-clés pertinents. Rejet automatique.',
                ], 422);
            }

            $valide = $pourcentage >= 60;
        }


        $ressource = Resource::create([
            'titre' => $request->titre,
            'description' => $request->description,
            'type' => $request->type,
            'chemin_fichier' => $cheminFichier,
            'fichier_hash' => $fichierHash,
            'auteur_id' => Auth::id(),
            'classe_id' => $request->classe_id,
            'subject_id' => $request->subject_id,
            'serie_id' => $request->serie_id,
            'valide' => $valide ?? false,
            'score_confiance' => round($pourcentage ?? 0),
            'downloads' => 0,
        ]);

        return ApiResponse::success($ressource, 'Ressource soumise avec succès. Merci pour votre contribution 👌👌😍', 201);
    }

    private function motsClesFusionnes($classeId, $serieId, $subjectId)
    {
        $classe = Classe::find($classeId);
        $serie = Serie::find($serieId);
        $matiere = Subject::find($subjectId);

        $mots = [];

        if ($classe) {
            $mots = array_merge($mots, $classe->mots_cles ?? []);
            $mots[] = strtolower($classe->nom);
        }

        if ($serie) {
            $mots = array_merge($mots, $serie->mots_cles ?? []);
            $mots[] = strtolower($serie->titre);
        }

        if ($matiere) {
            $mots = array_merge($mots, $matiere->mots_cles ?? []);
            $mots[] = strtolower($matiere->nom);
        }

        return array_unique(array_map('strtolower', $mots));
    }

    private function extractTextFromFile($fichierPath, $type)
    {
        if ($type === 'pdf') {
            $parser = new Parser();
            $pdf = $parser->parseFile($fichierPath);
            return strtolower($pdf->getText());
        }

        if (in_array($type, ['jpg', 'jpeg', 'png'])) {
            $output = shell_exec("tesseract " . escapeshellarg($fichierPath) . " stdout -l fra+eng");
            return strtolower($output);
        }

        return '';
    }

    private function pourcentageMotsTrouves($contenu, $motsCles)
    {
        $trouve = 0;
        foreach ($motsCles as $mot) {
            if (str_contains($contenu, strtolower($mot))) {
                $trouve++;
            }
        }

        if (count($motsCles) === 0) return 0;

        return ($trouve / count($motsCles)) * 100;
    }
    /**
     * Afficher une ressource spécifique.
     */
    public function show($id)
    {
        $ressource = Resource::find($id);

        if (!$ressource) {
            return ApiResponse::notFound('Ressource non trouvée.');
        }

        return ApiResponse::success($ressource);
    }

    /**
     * Télécharger une ressource.
     */
    public function download($id)
    {
        $ressource = Resource::find($id);

        if (!$ressource) {
            return ApiResponse::notFound('Ressource non trouvée.');
        }

        if (!$ressource->valide) {
            return ApiResponse::error('Ressource non validée.', null, 403);
        }

        $ressource->increment('downloads');

        if (Str::startsWith($ressource->chemin_fichier, 'http')) {
            return redirect($ressource->chemin_fichier);
        }

        return Storage::disk('public')->download($ressource->chemin_fichier);
    }

    /**
     * Supprimer une ressource.
     */
    public function destroy($id)
    {
        $ressource = Resource::find($id);

        if (!$ressource) {
            return ApiResponse::notFound('Ressource non trouvée.');
        }

        if ($ressource->auteur_id !== Auth::id()) {
            return ApiResponse::unauthorized('Non autorisé.');
        }

        if ($ressource->chemin_fichier && !Str::startsWith($ressource->chemin_fichier, 'http')) {
            Storage::disk('public')->delete($ressource->chemin_fichier);
        }

        $ressource->delete();

        return ApiResponse::success(null, 'Ressource supprimée.');
    }
}
