@extends('layouts.app')

@section('content')
    <!-- Paste seluruh <div class="container"> ... sampai </div> dari HTML ke sini -->
    <div class="container">
        <div class="header">
            <h1>UMKM GO</h1>
            <div class="nav">
                <button class="nav-btn active" onclick="showSection('search')">Pencarian & Filter</button>
                <button class="nav-btn" onclick="showSection('insert')">Insert Data</button>
                <button class="nav-btn" onclick="showSection('categorize')">Auto Kategorisasi</button>
                <button class="nav-btn" onclick="showSection('stats')">Statistik</button>
            </div>
        </div>
        <div class="content">
            <!-- Section Pencarian & Filter -->
            <div id="search" class="section active">
                <div class="search-filters">
                    <h3 style="margin-bottom: 20px; color: #495057;">Filter & Pencarian Data</h3>
                    {{-- Form untuk pencarian --}}
                    <form id="searchForm" method="POST" action="{{ route('umkm.post') }}">
                        @csrf
                        <div class="filter-grid">
                            <div class="form-group">
                                <label>Nama Toko *</label>
                                <input type="text" 
                                    name="search" 
                                    id="filterNama" 
                                    placeholder="Cari nama toko..." 
                                    value="{{ request('search') }}"
                                    required>
                            <small class="text-muted">* Wajib diisi</small>
                            </div>
                            <div class="form-group">
                                <label>Kategori</label>
                                <select name="kategori" id="filterKategori">
                                    <option value="">Semua Kategori</option>
                                    @foreach(['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','-'] as $kat)
                                        <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>
                                            {{ $kat }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Kecamatan</label>
                                <input type="text" 
                                    name="kecamatan" 
                                    id="filterKecamatan" 
                                    placeholder="Cari kecamatan..." 
                                    value="{{ request('kecamatan') }}">
                            </div>
                            
                            <div class="form-group">
                                <label>Desa</label>
                                <input type="text" 
                                    name="desa" 
                                    id="filterDesa" 
                                    placeholder="Cari desa..." 
                                    value="{{ request('desa') }}">
                            </div>
                        </div>
                        
                        <div style="text-align: center;">
                            <button type="submit" class="btn" id="searchBtn">
                            <span id="searchText">Cari</span>
                            <span id="searchLoading" style="display: none;">
                                <i class="spinner"></i> Mencari...
                            </span>
                        </button type="button" class="btn btn-secondary" id="resetBtn">
                            <a href="{{ route('umkm') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>

                {{-- Alert untuk error validasi --}}
                @if ($errors->any())
                    <div id="alertContainer" class="alert alert-danger">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                {{-- Search info --}}
                {{-- <div id="searchInfo" style="display: none;" class="alert alert-info">
                    <strong>Hasil Pencarian:</strong> 
                    <span id="searchResultText"></span>
                </div> --}}

                {{-- Alert untuk notifikasi pencarian --}}
                @if(request()->hasAny(['search', 'kategori', 'kecamatan', 'desa']))
                    <div id="searchInfo" class="alert alert-info">
                        <strong>Hasil Pencarian:</strong>
                        <span id="searchResultText">
                            Ditemukan {{ $umkms->total() }} data
                            @if(request('search'))
                                untuk nama toko "{{ request('search') }}"
                            @endif
                            @if(request('kategori'))
                                dengan kategori "{{ request('kategori') }}"
                            @endif
                            @if(request('kecamatan'))
                                di kecamatan "{{ request('kecamatan') }}"
                            @endif
                            @if(request('desa'))
                                di desa "{{ request('desa') }}"
                            @endif
                        </span> 
                    </div>
                @endif

                <div class="table-container">
                    <div id="loadingOverlay" style="display: none;">
                        <div class="loading-spinner">
                            <i class="spinner"></i>
                            <p>Memuat data...</p>
                        </div>
                    </div>
                    <table id="dataTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Toko</th>
                                <th>Alamat</th>
                                <th>Koordinat</th>
                                <th>Kategori</th>
                                <th>Kecamatan</th>
                                <th>Desa</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            @include('partials.table', ['umkms' => $umkms])
                            {{-- @forelse($umkms as $index => $umkm)
                                <tr>
                                    <td>{{ $umkms->firstItem() + $index }}</td>
                                    <td>{{ $umkm->nama ?? '-' }}</td>
                                    <td>{{ $umkm->alamat ?? '-' }}</td>
                                    <td>{{ $umkm->koordinat ?? '-' }}</td>
                                    <td>{{ $umkm->kategori ?? '-' }}</td>
                                    <td>{{ $umkm->kecamatan ?? '-' }}</td>
                                    <td>{{ $umkm->desa ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('umkm.show', $umkm->id) }}" class="btn btn-sm">Detail</a>
                                        <a href="{{ route('umkm.edit', $umkm->id) }}" class="btn btn-sm btn-secondary">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" style="text-align: center;">
                                        @if(request()->hasAny(['search', 'kategori', 'kecamatan', 'desa']))
                                            Tidak ada data yang sesuai dengan pencarian
                                        @else
                                            Belum ada data UMKM
                                        @endif
                                    </td>
                                </tr>
                            @endforelse --}}
                        </tbody>
                    </table>
                </div>
                <div id="paginationContainer">
                    @include('partials.pagination', ['umkms' => $umkms])
                </div>
                {{-- <div class="pagination" id="pagination">
                    <!-- Pagination akan dimuat di sini -->
                </div> --}}
            </div>

            <!-- Section Insert Data -->
            <div id="insert" class="section">
                <div id="loginSection">
                    <div class="login-form">
                        <h3 style="text-align: center; margin-bottom: 20px;">Autentikasi</h3>
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" id="username" placeholder="Masukkan username">
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" id="password" placeholder="Masukkan password">
                        </div>
                        <div style="text-align: center; margin-top: 20px;">
                            <button class="btn" onclick="login()">Login</button>
                        </div>
                        <div style="text-align: center; margin-top: 15px; font-size: 12px; color: #6c757d;">
                            Demo: username = admin, password = 123456
                        </div>
                    </div>
                </div>

                <div id="insertSection" style="display: none;">
                    <div style="max-width: 600px; margin: 0 auto;">
                        <h3 style="margin-bottom: 20px; text-align: center;">Tambah Data Toko Baru</h3>
                        <div id="insertAlert"></div>
                        
                        <div class="form-group">
                            <label>Nama Toko</label>
                            <input type="text" id="newNama" placeholder="Masukkan nama toko">
                        </div>
                        <div class="form-group">
                            <label>Alamat</label>
                            <input type="text" id="newAlamat" placeholder="Masukkan alamat toko">
                        </div>
                        <div class="form-group">
                            <label>Koordinat</label>
                            <input type="text" id="newKoordinat" placeholder="Contoh: -6.2088, 106.8456">
                        </div>
                        <div class="form-group">
                            <label>Kategori </label>
                            <select id="newKategori">
                                <option value="">Pilih Kategori</option>
                                <option value="Makanan">Makanan</option>
                                <option value="Minuman">Minuman</option>
                                <option value="Elektronik">Elektronik</option>
                                <option value="Fashion">Fashion</option>
                                <option value="Kesehatan">Kesehatan</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Kecamatan</label>
                            <input type="text" id="newKecamatan" placeholder="Masukkan kecamatan">
                        </div>
                        <div class="form-group">
                            <label>Desa</label>
                            <input type="text" id="newDesa" placeholder="Masukkan desa">
                        </div>
                        
                        <div style="text-align: center; margin-top: 20px;">
                            <button class="btn" onclick="insertData()">Simpan Data</button>
                            <button class="btn btn-secondary" onclick="logout()">Logout</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Auto Kategorisasi -->
            <div id="categorize" class="section">
                <h3 style="margin-bottom: 20px;">Auto Kategorisasi Berdasarkan Keyword</h3>
                
                <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                    <h4>Keyword yang Sudah Ditentukan:</h4>
                    <div style="margin-top: 10px;">
                        <span class="keyword-item">warung, warteg, rumah makan → Makanan</span>
                        <span class="keyword-item">kopi, teh, jus, minuman → Minuman</span>
                        <span class="keyword-item">hp, laptop, elektronik, gadget → Elektronik</span>
                        <span class="keyword-item">baju, sepatu, tas, fashion → Fashion</span>
                        <span class="keyword-item">apotek, obat, kesehatan → Kesehatan</span>
                    </div>
                </div>

                <div style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                    <p><strong>Total toko tanpa kategori:</strong> <span id="uncategorizedCount">0</span></p>
                    <div class="progress-bar">
                        <div class="progress-fill" id="progressFill"></div>
                    </div>
                    <p id="progressText">Siap untuk memulai kategorisasi</p>
                </div>

                <div style="text-align: center;">
                    <button class="btn" onclick="startCategorization()">Mulai Auto Kategorisasi</button>
                </div>

                <div id="categorizationResults" style="margin-top: 20px;"></div>
            </div>

            <!-- Section Statistik -->
            <div id="stats" class="section">
                <h3 style="margin-bottom: 20px;">Statistik Data Toko</h3>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3 id="totalToko">{{ $stats['total_umkm'] }}</h3>
                        <p>Total Toko</p>
                    </div>
                    <div class="stat-card">
                        <h3 id="totalKategori">{{ $stats['total_kategori'] }}</h3>
                        <p>Kategori Tersedia</p>
                    </div>
                    <div class="stat-card">
                        <h3 id="totalKecamatan">{{ $stats['total_kecamatan'] }}</h3>
                        <p>Kecamatan</p>
                    </div>
                    <div class="stat-card">
                        <h3 id="totalDesa">{{ $stats['total_desa'] }}</h3>
                        <p>Desa</p>
                    </div>
                </div>

                {{-- <div style="background: white; padding: 20px; border-radius: 10px;">
                    <h4 style="margin-bottom: 15px;">Distribusi Kategori</h4>
                    <div id="categoryStats"></div>
                </div> --}}

                <h4>Statistik Kategori</h4>
                {{-- <canvas id="kategoriChart" width="100" height="100"></canvas> --}}

                <div style="overflow-x:auto;">
                    <canvas id="kategoriChart" height="120"></canvas>
                </div>

                <h4>Statistik Kecamatan</h4>
                <div style="overflow-x:auto;">
                    <canvas id="kecamatanChart" height="120"></canvas>
                </div>
                

            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/umkm.css') }}">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const kategoriData = @json($stats['kategori_stats']);
        const kecamatanData = @json($stats['kecamatan_stats']);
    </script>

    <script src="{{ asset('js/umkm.js') }}"></script>
    {{-- <script src="{{ asset('js/umkm_2.js') }}"></script> --}}
@endpush