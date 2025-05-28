<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
        $this->authorizeResource(User::class);
    }

    public function index(): AnonymousResourceCollection
    {
        $users = $this->userService->getPaginatedUsers();
        return UserResource::collection($users);
    }

    public function show(string $id): UserResource
    {
        $user = $this->userService->getById($id);
        return new UserResource($user);
    }

    public function store(UserRequest $request): UserResource
    {
        $user = $this->userService->create($request->validated());
        return new UserResource($user);
    }

    public function update(UserRequest $request, string $id): UserResource
    {
        $user = $this->userService->update($id, $request->validated());
        return new UserResource($user);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->userService->delete($id);
        return response()->json(null, 204);
    }
}