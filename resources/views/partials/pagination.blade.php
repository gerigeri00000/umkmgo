{{-- partials/pagination.blade.php --}}
@if ($umkms instanceof \Illuminate\Pagination\Paginator || $umkms instanceof \Illuminate\Pagination\LengthAwarePaginator)
    @if($umkms->hasPages())
        <div class="pagination">
            @if(!$umkms->onFirstPage())
                <button class="page-link" data-page="{{ $umkms->currentPage() - 1 }}">‹</button>
            @endif

            @foreach ($umkms->getUrlRange(1, $umkms->lastPage()) as $page => $url)
                @if ($page == $umkms->currentPage())
                    <span class="page-link active">{{ $page }}</span>
                @else
                    <button class="page-link" data-page="{{ $page }}">{{ $page }}</button>
                @endif
            @endforeach

            @if($umkms->hasMorePages())
                <button class="page-link" data-page="{{ $umkms->currentPage() + 1 }}">›</button>
            @endif
        </div>
    @endif


@endif
