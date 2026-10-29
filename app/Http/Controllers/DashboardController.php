<?php

namespace App\Http\Controllers;

use App\Http\Requests\DashboardRequest;
use App\Services\DashboardService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(DashboardRequest $request, DashboardService $dashboardService): Response|RedirectResponse
    {
        if ($request->user()?->role_id === 1) {
            return redirect()->route('berkas-sidang-nol.index');
        }

        return Inertia::render('Dashboard/index', [
            'filters' => $request->filters(),
            ...$dashboardService->overview($request->filters()),
        ]);
    }
}
