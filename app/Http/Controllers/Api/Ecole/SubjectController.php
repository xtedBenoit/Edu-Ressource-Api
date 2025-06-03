<?php

namespace App\Http\Controllers\Api\Ecole;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ecole\Subject\StoreSubjectRequest;
use App\Http\Requests\Ecole\Subject\UpdateSubjectRequest;
use App\Models\Subject;

class SubjectController extends Controller
{
    /**
     * Afficher la liste des matieres.
     */
    public function index()
    {
        return ApiResponse::success(Subject::with(['resources', 'series', 'classes'])->get());
    }

    /**
     * Ajouter une nouvelle matiere.
     */
    public function store(StoreSubjectRequest $request)
    {
        $subject = Subject::create($request->validated());
        return ApiResponse::success($subject, 'Matière créée avec succès', 201);
    }

    /**
     * Affiche les details d'une matiere.
     */
    public function show($id)
    {
        $subject = Subject::with(['resources', 'series', 'classes'])->find($id);
        if (!$subject) return ApiResponse::notFound();
        return ApiResponse::success($subject);
    }

    /**
     * Mettre à jour un matiere.
     */
    public function update(UpdateSubjectRequest $request, $id)
    {
        $subject = Subject::find($id);
        if (!$subject) return ApiResponse::notFound();
        $subject->update($request->validated());
        return ApiResponse::success($subject, 'Matière mise à jour');
    }

    /**
     * Supprime une matiere.
     */
    public function destroy($id)
    {
        $subject = Subject::find($id);
        if (!$subject) return ApiResponse::notFound();
        $subject->delete();
        return ApiResponse::success(null, 'Matière supprimée');
    }
}