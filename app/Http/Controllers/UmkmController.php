<?php

namespace App\Http\Controllers;

use App\Models\Umkm;
use Illuminate\Http\Request;

class UmkmController extends Controller
{
    public function index(Request $request)
    {
        if (
            $request->has(['search', 'kategori', 'kecamatan', 'desa']) &&
            ($request->search || $request->kategori || $request->kecamatan || $request->desa)
        ) {

            $request->validate([
                'search' => 'required|string|min:3',
            ], [
                'search.required' => 'Nama toko harus diisi untuk melakukan pencarian!'
            ]);
        }

        $umkms = collect();

        if ($request->filled('search') || $request->filled('kategori') || $request->filled('kecamatan') || $request->filled('desa')) {
            $umkms = Umkm::query()
                ->when($request->search, function ($query, $search) {
                    return $query->where('nama', 'like', "%{$search}%");
                })
                ->when($request->kategori, function ($query, $kategori) {
                    return $query->where('kategori', $kategori);
                })
                ->when($request->kecamatan, function ($query, $kecamatan) {
                    return $query->where('kecamatan', 'like', "%{$kecamatan}%");
                })
                ->when($request->desa, function ($query, $desa) {
                    return $query->where('desa', 'like', "%{$desa}%");
                })
                ->orderBy('nama')
                ->paginate(10)
                ->withQueryString();

            $umkms->getCollection()->transform(function ($item) {
                // Cek dan parsing koordinat
                if ($item->koordinat && str_starts_with($item->koordinat, 'Point')) {
                    [$lng, $lat] = explode(' ', str_replace(['Point (', ')'], '', $item->koordinat));
                    $item->google_maps_url = "https://www.google.com/maps?q=$lat,$lng";
                } else {
                    $item->google_maps_url = null;
                }
                return $item;
            });
        }

        $stats = [
            'total_umkm' => Umkm::count(),
            'total_kategori' => Umkm::whereNotNull('kategori')->distinct('kategori')->count(),
            'total_kecamatan' => Umkm::distinct('kecamatan')->count(),
            'total_desa' => Umkm::distinct('desa')->count(),

            // Statistik per kategori (untuk pie chart)
            'kategori_stats' => Umkm::selectRaw('kategori, COUNT(*) as count')
                ->groupBy('kategori')
                ->orderBy('count', 'desc')
                ->get(),

            // Statistik per kecamatan (untuk pie chart)
            'kecamatan_stats' => Umkm::selectRaw('kecamatan, COUNT(*) as count')
                ->groupBy('kecamatan')
                ->orderBy('count', 'desc')
                ->get()
        ];


        // Jika request AJAX, return JSON
        if ($request->ajax()) {
            return response()->json([
                'html' => view('partials.table', compact('umkms'))->render(),
                'pagination' => view('partials.pagination', compact('umkms'))->render(),
            ]);
        }

        return view('umkm', [
            'umkms' => $umkms,
            'stats' => $stats,
            'filters' => $request->only(['search', 'kategori', 'kecamatan', 'desa'])
        ]);
    }

    public function loadData(Request $request)
    {
        $umkms = Umkm::orderBy('nama')->paginate(10);

        return response()->json([
            'success' => true,
            'html' => view('partials.table', compact('umkms'))->render(),
            'pagination' => view('partials.pagination', compact('umkms'))->render(),
            'total' => $umkms->total()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string',
            'koordinat' => 'required|string|regex:/^-?\d+\.?\d*,\s*-?\d+\.?\d*$/',
            'kategori' => 'nullable|string|max:100',
            'kecamatan' => 'required|string|max:100',
            'desa' => 'required|string|max:100',
            'telepon' => 'nullable|string|max:20',
            'deskripsi' => 'nullable|string'
        ]);

        Umkm::create($validated);

        return redirect()->route('umkm')->with('success', 'Data UMKM berhasil ditambahkan!');
    }

    public function update(Request $request, Umkm $umkm)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string',
            'koordinat' => 'required|string|regex:/^-?\d+\.?\d*,\s*-?\d+\.?\d*$/',
            'kategori' => 'nullable|string|max:100',
            'kecamatan' => 'required|string|max:100',
            'desa' => 'required|string|max:100',
            'telepon' => 'nullable|string|max:20',
            'deskripsi' => 'nullable|string'
        ]);

        $umkm->update($validated);

        return redirect()->route('umkm.index')->with('success', 'Data UMKM berhasil diupdate!');
    }

    public function destroy(Umkm $umkm)
    {
        $umkm->delete();
        return redirect()->route('umkm.index')->with('success', 'Data UMKM berhasil dihapus!');
    }

    public function autoCategorize()
    {
        $keywords = [
            'Makanan' => ['warung', 'warteg', 'rumah makan', 'resto', 'restaurant', 'nasi', 'bakso', 'soto'],
            'Minuman' => ['kopi', 'teh', 'jus', 'minuman', 'cafe', 'kedai kopi', 'bubble tea'],
            'Elektronik' => ['hp', 'laptop', 'elektronik', 'gadget', 'komputer', 'handphone'],
            'Fashion' => ['baju', 'sepatu', 'tas', 'fashion', 'clothing', 'butik', 'distro'],
            'Kesehatan' => ['apotek', 'obat', 'kesehatan', 'farmasi', 'klinik', 'dokter']
        ];

        $uncategorized = Umkm::whereNull('kategori')->orWhere('kategori', '')->get();
        $categorized = 0;

        foreach ($uncategorized as $umkm) {
            $namaLower = strtolower($umkm->nama);

            foreach ($keywords as $category => $categoryKeywords) {
                foreach ($categoryKeywords as $keyword) {
                    if (strpos($namaLower, $keyword) !== false) {
                        $umkm->update(['kategori' => $category]);
                        $categorized++;
                        break 2;
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$categorized} UMKM berhasil dikategorikan dari {$uncategorized->count()} UMKM tanpa kategori"
        ]);
    }

    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240'
        ]);

        $file = $request->file('csv_file');
        $csvData = array_map('str_getcsv', file($file->getRealPath()));
        $header = array_shift($csvData);

        $imported = 0;
        foreach ($csvData as $row) {
            if (count($row) >= 6) {
                Umkm::create([
                    'nama' => $row[0] ?? '',
                    'alamat' => $row[1] ?? '',
                    'koordinat' => $row[2] ?? '',
                    'kategori' => $row[3] ?? null,
                    'kecamatan' => $row[4] ?? '',
                    'desa' => $row[5] ?? '',
                ]);
                $imported++;
            }
        }

        return redirect()->route('umkm.index')->with('success', "{$imported} data berhasil diimport!");
    }
}
