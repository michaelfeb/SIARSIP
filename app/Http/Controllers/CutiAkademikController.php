<?php

namespace App\Http\Controllers;

use App\Http\Requests\CutiAkademikRequest;
use App\Models\Letter;
use App\Models\LetterAttachment;
use App\Services\CutiAkademikService;
use App\Services\LetterAttachmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CutiAkademikController extends Controller
{
    public function index(CutiAkademikRequest $request, CutiAkademikService $service): Response
    {
        $filters = $request->filters();

        return Inertia::render('CutiAkademik/Index', [
            'letters' => $service->datatable($request->user(), $filters),
            'filters' => $filters,
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('CutiAkademik/Form', ['mode' => 'create', 'applicant' => $request->user()]);
    }

    public function store(CutiAkademikRequest $request, CutiAkademikService $service): RedirectResponse
    {
        $letter = $service->create($request->user(), $request->payload());

        return redirect()->route('cuti-akademik.show', $letter)->with('success', 'Draft Cuti Akademik berhasil disimpan.');
    }

    public function show(Request $request, Letter $letter, CutiAkademikService $service): Response
    {
        return Inertia::render('CutiAkademik/Show', ['letter' => $service->findForDisplay($letter, $request->user())]);
    }

    public function edit(Request $request, Letter $letter, CutiAkademikService $service): Response
    {
        $letter = $service->findForDisplay($letter, $request->user());
        $this->authorize('update', $letter);

        return Inertia::render('CutiAkademik/Form', ['mode' => 'edit', 'letter' => $letter, 'applicant' => $request->user()]);
    }

    public function update(CutiAkademikRequest $request, Letter $letter, CutiAkademikService $service): RedirectResponse
    {
        $service->update($letter, $request->user(), $request->payload());

        return redirect()->route('cuti-akademik.show', $letter)->with('success', 'Draft Cuti Akademik berhasil diperbarui.');
    }

    public function destroy(Request $request, Letter $letter, CutiAkademikService $service): RedirectResponse
    {
        $service->delete($letter, $request->user());

        return redirect()->route('cuti-akademik.index')->with('success', 'Draft Cuti Akademik dihapus.');
    }

    public function submit(Request $request, Letter $letter, CutiAkademikService $service): RedirectResponse
    {
        $service->ajuan($letter, $request->user());

        return redirect()->route('cuti-akademik.show', $letter)->with('success', 'Permohonan Cuti Akademik telah dikirim ke PRODI.');
    }

    public function review(Request $request, Letter $letter, CutiAkademikService $service): Response
    {
        $letter = $service->findForDisplay($letter, $request->user());

        return Inertia::render('CutiAkademik/Ajuan', [
            'letter' => $letter,
            'signatories' => $service->isFinalizationStage($letter) ? $service->availableFinalSignatories($request->user(), $letter) : [],
        ]);
    }

    public function decide(CutiAkademikRequest $request, Letter $letter, CutiAkademikService $service): RedirectResponse
    {
        $service->decide($letter, $request->user(), $request->string('decision')->toString(), $request->input('note'));

        return redirect()->route('cuti-akademik.index')->with('success', 'Keputusan permohonan berhasil disimpan.');
    }

    public function finalize(CutiAkademikRequest $request, Letter $letter, CutiAkademikService $service): RedirectResponse
    {
        $service->finalize($letter, $request->user(), $request->validated());

        return redirect()->route('cuti-akademik.index')->with('success', 'Surat Cuti Akademik berhasil dibuat dan diarsipkan.');
    }

    public function showAttachment(Request $request, Letter $letter, LetterAttachment $attachment, CutiAkademikService $service, LetterAttachmentService $attachments)
    {
        $attachment = $service->findViewableAttachment($letter, $attachment, $request->user());
        abort_unless($attachments->exists($attachment), 404, 'Dokumen tidak ditemukan.');

        return Storage::disk('secure')->response($attachment->file_path, $attachment->original_name, [
            'Content-Type' => $attachment->mime,
            'Content-Disposition' => 'inline; filename="'.$attachment->original_name.'"',
        ]);
    }

    public function downloadFinalLetter(Request $request, Letter $letter, CutiAkademikService $service)
    {
        $letter = $service->findForDisplay($letter, $request->user());
        abort_unless($letter->status === 'ARCHIVED' && $letter->final_pdf_path && Storage::disk('secure')->exists($letter->final_pdf_path), 404, 'Surat belum tersedia.');

        return Storage::disk('secure')->download($letter->final_pdf_path, 'surat-cuti-akademik-'.$letter->id.'.pdf');
    }
}
