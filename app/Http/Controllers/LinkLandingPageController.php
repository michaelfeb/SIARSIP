<?php

namespace App\Http\Controllers;

use App\Models\LinkLandingPage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LinkLandingPageController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $query = LinkLandingPage::query()
            ->when($search, function ($query) use ($search) {
                $query->where('nama', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%")
                    ->orWhere('link', 'like', "%{$search}%");
            });

        $links = $query->orderBy('no_urut')->paginate($perPage)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($links);
        }

        return Inertia::render('LinkLandingPage/Index', [
            'links' => $links,
        ]);
    }

    public function create()
    {
        $lastNoUrut = LinkLandingPage::max('no_urut') ?? 0;
        $nextNoUrut = $lastNoUrut + 1;

        return Inertia::render('LinkLandingPage/Form', [
            'defaultNoUrut' => $nextNoUrut,
        ]);
    }


    public function save(Request $request, $id = null)
    {
        $rules = [
            'nama' => 'required|string|max:255',
            'deskripsi' => 'required|string|max:255',
            'no_urut' => 'required|integer',
            'status' => 'required|boolean',
            'link' => 'required|url|max:255',
            'icon' => 'required|string|max:100',
            'color' => 'required|string|max:100',
        ];

        $messages = [
            'nama.required' => 'Nama wajib diisi.',
            'no_urut.required' => 'Nomor urut wajib diisi.',
            'no_urut.integer' => 'Nomor urut harus berupa angka.',
            'status.required' => 'Status wajib diisi.',
            'status.boolean' => 'Status harus berupa boolean.',
            'link.required' => 'Link wajib diisi.',
            'link.url' => 'Link harus berupa URL yang valid.',
            'icon.required' => 'Ikon wajib dipilih.',
            'deskripsi.required' => 'Deskripsi wajib diisi.',
            'icon.string' => 'Ikon harus berupa teks.',
            'icon.max' => 'Ikon maksimal 100 karakter.',
            'color.required' => 'Warna ikon wajib dipilih.',
            'color.string' => 'Warna ikon harus berupa teks.',
            'color.max' => 'Warna maksimal 100 karakter.',
        ];

        $validated = $request->validate($rules, $messages);

        if ($id) {
            $link = LinkLandingPage::findOrFail($id);
            $link->update($validated);

            return redirect()->route('link-landing-page.index')->with('success', 'Link berhasil diperbarui.');
        } else {
            LinkLandingPage::create($validated);

            return redirect()->route('link-landing-page.index')->with('success', 'Link berhasil ditambahkan.');
        }
    }

    public function edit($id)
    {
        $link = LinkLandingPage::findOrFail($id);

        return Inertia::render('LinkLandingPage/Form', [
            'link' => $link,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, LinkLandingPage $linkLandingPage)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:255',
            'no_urut' => 'required|integer',
            'status' => 'required|boolean',
            'link' => 'required|url|max:255',
        ]);

        $linkLandingPage->update($validated);

        return redirect()->route('link-landing-page.index')->with('success', 'Link berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $link = LinkLandingPage::findOrFail($id);

        $link->delete();

        return response()->json([
            'message' => 'Link berhasil dihapus.',
            'status' => 'success',
        ]);
    }


    public function toggle($id)
    {
        $link = LinkLandingPage::findOrFail($id);
        $link->status = !$link->status;
        $link->save();

        return response()->json(['success' => true]);
    }
}
