<?php

namespace App\Http\Controllers\Api\Resource;

use App\Enums\ResourceType;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Resource\StoreRessourceRequest;
use App\Models\Classe;
use App\Models\Resource;
use App\Models\Serie;
use App\Models\Subject;
use App\Services\Ressource\ResourceValidatorService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
    public function store(StoreRessourceRequest $request)
    {
        $fichier = $request->file('fichier');

        // Hash du fichier pour vérification des doublons (AVANT stockage)
        $fichierHash = md5_file($fichier->getRealPath());
        if (Resource::where('fichier_hash', $fichierHash)->exists()) {
            return ApiResponse::error('Ce fichier a déjà été publié.', 409);
        }

        // Construction du nom de fichier : titre_date.extension
        $dateSuffix = Carbon::now()->format('Ymd_His');
        $extension = $fichier->getClientOriginalExtension();
        $nomFichierOriginal = pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
        $nomFichierNettoye = Str::slug($nomFichierOriginal);
        $nomFichierFinal = $nomFichierNettoye . '_' . $dateSuffix . '.' . $extension;

        // Sauvegarde du fichier dans "storage/app/public/ressources"
        $cheminFichier = $fichier->storeAs('ressources', $nomFichierFinal, 'public');
        if (!$cheminFichier) {
            return ApiResponse::error('Échec de l\'enregistrement du fichier.', 500);
        }

        // Analyse du fichier (via service)
        $validator = new ResourceValidatorService();
        $classe = Classe::find($request->classe_id);
        $subject = Subject::find($request->subject_id);
        $serie = Serie::find($request->serie_id);

        $analyse = $validator->analyser(
            storage_path('app/public/' . $cheminFichier),
            $extension,
            $classe,
            $subject,
            $serie
        );

        // Création de la ressource
        $ressource = Resource::create([
            'titre' => $request->titre,
            'description' => $request->description,
            'type_fichier' => $request->type_fichier,
            'type_ressource' => $analyse['type_ressource'],
            'chemin_fichier' => $cheminFichier,
            'fichier_hash' => $fichierHash,
            'auteur_id' => auth()->id(),
            'classe_id' => $request->classe_id,
            'subject_id' => $request->subject_id,
            'serie_id' => $request->serie_id,
            'valide' => $analyse['valide'],
            'score_confiance' => $analyse['score'],
            'commentaire_validation' => $analyse['commentaire'],
            'downloads' => 0,
        ]);

        auth()->user()->addDownloadBonus(3); // Bonus de 3 téléchargements

        return ApiResponse::success(
            $ressource,
            $analyse['valide']
                ? 'Ressource validée automatiquement 🎉 - Type: ' . ResourceType::from($analyse['type_ressource'])->label()
                : 'Ressource en attente de validation humaine 🔎 - Type: ' . ResourceType::from($analyse['type_ressource'])->label(),
            201
        );
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
        $user = auth()->user();

        if (!$ressource) {
            return ApiResponse::notFound('Ressource non trouvée.');
        }

        if (!$ressource->valide) {
            return ApiResponse::error('Ressource non validée.', null, 403);
        }

        // Réinitialisation du quota si besoin
        $user->resetDownloadQuotaIfNeeded();

        if ($user->downloads_remaining <= 0) {
            return ApiResponse::error("Quota atteint. Ajoute une ressource pour débloquer plus !", 403);
        }

        $user->decrementDownloadQuota();
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

        if ($ressource->auteur_id !== auth()->id()) {
            return ApiResponse::unauthorized('Non autorisé.');
        }

        if ($ressource->chemin_fichier && !Str::startsWith($ressource->chemin_fichier, 'http')) {
            Storage::disk('public')->delete($ressource->chemin_fichier);
        }

        $ressource->delete();

        return ApiResponse::success(null, 'Ressource supprimée.');
    }
}
