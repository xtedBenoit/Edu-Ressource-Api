<?php

namespace App\Http\Controllers\Api\Ecole;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ecole\Classe\StoreClasseRequest;
use App\Http\Requests\Ecole\Classe\UpdateClasseRequest;
use App\Models\Classe;
use App\Models\Subject;
use Illuminate\Http\Request;
use MongoDB\BSON\ObjectId;


class ClasseController extends Controller
{
    /**
     * Afficher la liste des classes.
     */

    public function index()
    {
        $classes = Classe::with(['resources', 'series'])->get();

        // Charger les subjects manuellement pour chaque classe
        // $classes->each(function ($classe) {
        //     if (!empty($classe->subject_ids)) {
        //         $ids = array_map(fn($id) => new ObjectId($id), $classe->subject_ids);
        //         $classe->setRelation('subjects', Subject::whereIn('_id', $ids)->get());
        //     } else {
        //         $classe->setRelation('subjects', collect([]));
        //     }
        // });

        return ApiResponse::success($classes);
    }


    /**
     * Ajouter une nouvelle classe.
     */
    public function store(StoreClasseRequest $request)
    {
        $classe = Classe::create($request->validated());
        return ApiResponse::success($classe, 'Classe créée', 201);
    }

    /**
     * Afficher les détails d'une classe.
     */
    public function show($id)
    {
        $classe = Classe::with(['resources', 'series', 'subjects'])->find($id);
        if (!$classe) return ApiResponse::notFound();
        return ApiResponse::success($classe);
    }

    /**
     * Mettre à jour une classe.
     */
    public function update(UpdateClasseRequest $request, $id)
    {
        $classe = Classe::find($id);
        if (!$classe) return ApiResponse::notFound();
        $classe->update($request->validated());
        return ApiResponse::success($classe, 'Classe mise à jour');
    }

    /**
     * Supprimer une classe.
     */
    public function destroy($id)
    {
        $classe = Classe::find($id);
        if (!$classe) return ApiResponse::notFound();
        $classe->delete();
        return ApiResponse::success(null, 'Classe supprimée');
    }
}
