<?php

namespace App\Http\Controllers\Api\Message;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ApiResponse;

class MessageController extends Controller
{
    /**
     * Lister les messages d'une ressource
     *
     */
    public function index($resourceId)
    {
        try {
            // Vérifier que la ressource existe dans MongoDB
            $resourceExists = Resource::where('_id', $resourceId)->exists();

            if (! $resourceExists) {
                return ApiResponse::error("La ressource demandée n'existe pas.", null, 404);
            }

            // Récupérer les messages liés à la ressource
            $messages = Message::where('resource_id', $resourceId)->latest()->get();

            return ApiResponse::success($messages);
        } catch (\Exception $e) {
            return ApiResponse::error('Erreur lors de la récupération des messages', $e->getMessage());
        }
    }

    /**
     * Poster un message (commentaire)
     *
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'resource_id' => 'required|string|exists:resources,_id',
                'contenu' => 'required|string|max:1000',
            ]);

            $user = Auth::user();

            $message = Message::create([
                'resource_id' => $request->resource_id,
                'contenu' => $request->contenu,
                'user_id' => $user->_id,
            ]);

            return ApiResponse::success($message, 'Message ajouté avec succès');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Erreur lors de l\'ajout du message', $e->getMessage());
        }
    }

    /**
     * Supprimer un message
     *
     * @param [type] $id
     * @return void
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $message = Message::findOrFail($id);

            if (!$message->canModify($user)) {
                return ApiResponse::unauthorized('Non autorisé à supprimer ce message');
            }

            $message->delete();
            return ApiResponse::success(null, 'Message supprimé avec succès');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::notFound('Message introuvable');
        } catch (\Exception $e) {
            return ApiResponse::error('Erreur lors de la suppression du message', $e->getMessage());
        }
    }

    /**
     * Modifier un message
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $message = Message::findOrFail($id);
            $user = Auth::user();

            if (!$message->canModify($user)) {
                return ApiResponse::unauthorized('Non autorisé à modifier ce message');
            }

            $request->validate([
                'contenu' => 'required|string|max:1000',
            ]);

            $message->update([
                'contenu' => $request->contenu,
            ]);

            return ApiResponse::success($message, 'Message mis à jour avec succès');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::notFound('Message introuvable');
        } catch (\Exception $e) {
            return ApiResponse::error('Erreur lors de la modification du message', $e->getMessage());
        }
    }
}
