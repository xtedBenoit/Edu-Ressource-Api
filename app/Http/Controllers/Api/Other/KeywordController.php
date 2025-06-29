<?php

namespace App\Http\Controllers\Api\Other;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Resource;
use Illuminate\Http\Request;

class KeywordController extends Controller
{
    public function suggest(Request $request)
    {
        $query = $request->get('query', '');

        if (strlen($query) < 2) {
            return response()->json([
                'status' => 'error',
                'message' => 'La requête doit contenir au moins 2 caractères.',
                'data' => []
            ]);
        }

        // Recherche textuelle MongoDB
        $results = Resource::raw(function ($collection) use ($query) {
            return $collection->aggregate([
                ['$match' => ['$text' => ['$search' => $query]]],
                ['$project' => ['mots_cles' => 1]],
                ['$limit' => 20]
            ]);
        });

        // Extraire tous les mots-clés et les aplatir
        $suggestions = [];
        foreach ($results as $doc) {
            if (isset($doc['mots_cles']) && is_array($doc['mots_cles'])) {
                $suggestions = array_merge($suggestions, $doc['mots_cles']);
            }
        }

        // Nettoyer et dédupliquer
        $unique = array_values(array_unique(array_filter($suggestions, function ($item) use ($query) {
            return str_contains(strtolower($item), strtolower($query));
        })));

        return ApiResponse::success(array_slice($unique, 0, 10), 'Suggestions récupérées');
    }
}
