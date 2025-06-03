<?php

namespace App\Http\Controllers\Api\Ecole;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ecole\Serie\StoreSerieRequest;
use App\Http\Requests\Ecole\Serie\UpdateSerieRequest;
use App\Models\Serie;
use Illuminate\Http\Request;

class SerieController extends Controller
{
    /**
     * Récupère la liste des séries.
     */
    public function index()
    {
        return ApiResponse::success(Serie::with(['resources', 'classe', 'subject', 'auteur'])->get());
    }

    /**
     * Crée une nouvelle série.
     */
    public function store(StoreSerieRequest $request)
    {
        $serie = Serie::create($request->validated());
        return ApiResponse::success($serie, 'Série créée', 201);
    }

    /**
     * ffiche les détails d'une série.
     */
    public function show($id)
    {
        $serie = Serie::with(['resources', 'classe', 'subject', 'auteur'])->find($id);
        if (!$serie) return ApiResponse::notFound();
        return ApiResponse::success($serie);
    }

    /**
     * Mettre à jour une série.
     */
    public function update(UpdateSerieRequest $request, $id)
    {
        $serie = Serie::find($id);
        if (!$serie) return ApiResponse::notFound();
        $serie->update($request->validated());
        return ApiResponse::success($serie, 'Série mise à jour');
    }

    /**
     * Supprime une série.
     */
    public function destroy($id)
    {
        $serie = Serie::find($id);
        if (!$serie) return ApiResponse::notFound();
        $serie->delete();
        return ApiResponse::success(null, 'Série supprimée');
    }
}