{{-- Partial View (resources/views/umkm/partials/table.blade.php) --}}

@forelse($umkms as $index => $umkm)
    <tr class="fade-in">
        <td>{{ $umkms->firstItem() + $index }}</td>
        <td>{{ $umkm->nama ?? '-' }}</td>
        <td>{{ $umkm->alamat ?? '-' }}</td>
        <td>{{ $umkm->koordinat ?? '-' }}</td>
        <td>{{ $umkm->kategori ?? '-' }}</td>
        <td>{{ $umkm->kecamatan ?? '-' }}</td>
        <td>{{ $umkm->desa ?? '-' }}</td>
        <td>
            <a href="{{ $umkm->google_maps_url ?? '#' }}" class="btn btn-sm" target="_blank">Google Maps</a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" style="text-align: center;" class="no-data">
            <div class="empty-state">
                <i class="icon-search" style="font-size: 48px; color: #6c757d;"></i>
                {{-- <h4>Tidak ada data ditemukan</h4>
                <p>Coba ubah kriteria pencarian Anda</p> --}}
            </div>
        </td>
    </tr>
@endforelse