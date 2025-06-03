<?php
// app/Http/Controllers/QuotaController.php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuotaController extends Controller
{
    /**
     * Affiche les quotas restants de l'utilisateur.
     */
    public function showQuota()
    {
        $user = Auth::user();
        $user->resetDownloadQuotaIfNeeded();

        return ApiResponse::success([
            'downloads_remaining' => $user->downloads_remaining,
            'next_reset' => $user->downloads_reset_at
                ? $user->downloads_reset_at->copy()->addMonth()->startOfMonth()->toDateString()
                : 'Non défini',
        ]);
    }
}
