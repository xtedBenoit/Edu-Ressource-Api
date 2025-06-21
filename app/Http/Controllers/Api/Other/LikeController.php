<?php

namespace App\Http\Controllers\Api\Other;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\Resource; // Pour vérifier l'existence de la ressource
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MongoDB\BSON\ObjectId;

class LikeController extends Controller
{
    /**
     * Liker ou unliker une ressource (toggle)
     */
    public function toggle(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return ApiResponse::error('Utilisateur non connecté.', null, 401);
            }

            $request->validate([
                'resource_id' => 'required|string',
            ]);

            $resourceId = $request->input('resource_id');

            // Convertir en ObjectId si nécessaire
            try {
                $resourceObjectId = new ObjectId($resourceId);
            } catch (\Exception $e) {
                return ApiResponse::error('ID de ressource invalide.');
            }

            // Vérifier que la ressource existe
            $resourceExists = Resource::where('_id', $resourceObjectId)->exists();
            if (!$resourceExists) {
                return ApiResponse::error("La ressource spécifiée n'existe pas.", null, 404);
            }

            // Chercher un like existant
            $existingLike = Like::where('user_id', $user->_id)
                ->where('resource_id', $resourceObjectId)
                ->first();

            if ($existingLike) {
                $existingLike->delete();
                return ApiResponse::success(['liked' => false], 'Like supprimé');
            } else {
                Like::create([
                    'user_id' => $user->_id,
                    'resource_id' => $resourceObjectId,
                ]);
                return ApiResponse::success(['liked' => true], 'Like ajouté');
            }
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return ApiResponse::error('Données invalides.', $ve->errors(), 422);
        } catch (\Exception $e) {
            // Logue l'erreur si besoin: Log::error($e);
            return ApiResponse::error('Une erreur est survenue lors du traitement du like.', $e->getMessage(), 500);
        }
    }

    /**
     * Obtenir le nombre de likes pour une ressource
     */
    public function count($resourceId)
    {
        try {
            // Convertir en ObjectId si nécessaire
            try {
                $resourceObjectId = new ObjectId($resourceId);
            } catch (\Exception $e) {
                return ApiResponse::error('ID de ressource invalide.');
            }

            // Vérifier que la ressource existe
            $resourceExists = Resource::where('_id', $resourceObjectId)->exists();
            if (!$resourceExists) {
                return ApiResponse::error("La ressource spécifiée n'existe pas.", null, 404);
            }

            $likeCount = Like::where('resource_id', $resourceObjectId)->count();
            return ApiResponse::success(['count' => $likeCount], 'Nombre de likes récupéré');
        } catch (\Exception $e) {
            // Logue l'erreur si besoin
            return ApiResponse::error('Erreur lors de la récupération du nombre de likes.', $e->getMessage(), 500);
        }
    }

    /**
     * Vérifier si l'utilisateur connecté a liké une ressource
     */
    public function check($resourceId)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return ApiResponse::error('Utilisateur non connecté.', null, 401);
            }

            // Convertir en ObjectId si nécessaire
            try {
                $resourceObjectId = new ObjectId($resourceId);
            } catch (\Exception $e) {
                return ApiResponse::error('ID de ressource invalide.');
            }

            // Vérifier que la ressource existe
            $resourceExists = Resource::where('_id', $resourceObjectId)->exists();
            if (!$resourceExists) {
                return ApiResponse::error("La ressource spécifiée n'existe pas.", null, 404);
            }

            $hasLiked = Like::where('user_id', $user->_id)
                ->where('resource_id', $resourceObjectId)
                ->exists();

            return ApiResponse::success(['has_liked' => $hasLiked], 'Statut de like récupéré');
        } catch (\Exception $e) {
            // Logue l'erreur si besoin
            return ApiResponse::error('Erreur lors de la vérification du like.', $e->getMessage(), 500);
        }
    }
}
