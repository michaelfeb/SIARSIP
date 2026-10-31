<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApprovalWorkflowRequest;
use App\Models\LetterType;
use App\Services\ApprovalWorkflowService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ApprovalWorkflowController extends Controller
{
    public function index(ApprovalWorkflowService $service): Response
    {
        return Inertia::render('AlurPersetujuanSurat/Index', $service->settings());
    }

    public function update(
        ApprovalWorkflowRequest $request,
        LetterType $letterType,
        ApprovalWorkflowService $service,
    ): RedirectResponse {
        $service->update($letterType, $request->payload());

        return redirect()
            ->route('approval-workflows.index')
            ->with('success', 'Alur persetujuan dan catatan proses berhasil diperbarui.');
    }
}
