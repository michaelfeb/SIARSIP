<?php

namespace App\Http\Controllers;

use App\Models\Carousel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CarouselController extends Controller
{
    //
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $query = Carousel::query()
            ->when($search, function ($query) use ($search) {
                $query->where('nama', 'like', "%{$search}%");
            });

        $carousel = $query->paginate($perPage)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($carousel);
        }

        return Inertia::render('Carousel/Index', [
            'carousel' => $carousel,
        ]);
    }

    public function create()
    {
        return Inertia::render('Carousel/Form',);
    }

    public function save(Request $request, $id = null)
    {

        $rules = [
            'nama' => 'required|string|max:255',
            'tanggal_publish' => 'required|date',
            'status' => 'required|boolean',
            'gambar' => $id ? 'nullable|image|mimes:jpeg,png,jpg|max:5120' : 'required|image|mimes:jpeg,png,jpg|max:5120',
        ];

        $messages = [
            'nama.required' => 'Nama wajib diisi.',
            'tanggal_publish.required' => 'Tanggal publikasi wajib diisi.',
            'tanggal_publish.date' => 'Tanggal publikasi harus berupa tanggal yang valid.',
            'status.required' => 'Status wajib diisi.',
            'gambar.required' => 'Gambar wajib diunggah.',
            'gambar.image' => 'File yang diunggah harus berupa gambar.',
            'gambar.mimes' => 'Format gambar harus JPEG, PNG, atau JPG.',
            'gambar.max' => 'Ukuran maksimal gambar adalah 5MB.',
        ];

        $validated = $request->validate($rules, $messages);

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $path = $file->store('carousel', 'public');
            $validated['gambar'] = $path;
        }

        if ($id) {
            $carousel = \App\Models\Carousel::findOrFail($id);

            if ($request->hasFile('gambar') && $carousel->gambar) {
                Storage::disk('public')->delete($carousel->gambar);
            }

            $carousel->update($validated);
        } else {
            $carousel = \App\Models\Carousel::create($validated);
        }

        return redirect()->route('carousel.index')->with('success', 'Carousel berhasil disimpan.');
    }

    public function edit($id)
    {
        $carousel = Carousel::findOrFail($id);

        return Inertia::render('Carousel/Form', [
            'carousel' => $carousel,
            'mode' => 'edit',
        ]);
    }

    public function toggle($id)
    {
        $carousel = \App\Models\Carousel::findOrFail($id);
        $carousel->status = !$carousel->status;
        $carousel->save();

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $carousel = Carousel::findOrFail($id);

        $carousel->delete();

        return response()->json([
            'message' => 'Jenis surat berhasil dihapus.',
            'status' => 'success',
        ]);
    }

    public function showImage($filename)
{
    $path = "carousel/{$filename}";

    if (!Storage::disk('public')->exists($path)) {
        abort(404, 'Gambar tidak ditemukan.');
    }

    return response()->file(storage_path("app/public/{$path}"));
}
}
