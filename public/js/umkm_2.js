// JavaScript untuk Seamless Search (di bagian bawah blade template)

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('searchForm');
    const tableBody = document.getElementById('tableBody');
    const paginationContainer = document.getElementById('paginationContainer');
    const loadingOverlay = document.getElementById('loadingOverlay');
    const alertContainer = document.getElementById('alertContainer');
    const searchInfo = document.getElementById('searchInfo');
    const searchBtn = document.getElementById('searchBtn');
    const resetBtn = document.getElementById('resetBtn');

    // CSRF Token untuk AJAX
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
    document.querySelector('input[name="_token"]')?.value;

    // Function untuk show loading
    function showLoading() {
        if (loadingOverlay) loadingOverlay.style.display = 'flex';
        const searchText = document.getElementById('searchText');
        const searchLoading = document.getElementById('searchLoading');
        if (searchText) searchText.style.display = 'none';
        if (searchLoading) searchLoading.style.display = 'inline';
        if (searchBtn) searchBtn.disabled = true;
    }

    // Function untuk hide loading
    function hideLoading() {
        loadingOverlay.style.display = 'none';
        document.getElementById('searchText').style.display = 'inline';
        document.getElementById('searchLoading').style.display = 'none';
        searchBtn.disabled = false;
    }

    // Function untuk show alert
    function showAlert(message, type = 'danger') {
        alertContainer.innerHTML = `
            <div class="alert alert-${type} fade-in">
                <button type="button" class="close" onclick="this.parentElement.remove()">×</button>
                ${message}
            </div>
        `;
        setTimeout(() => {
            const alert = alertContainer.querySelector('.alert');
            if (alert) alert.remove();
        }, 5000);
    }

    // Function untuk update search info
    function updateSearchInfo(filters, total) {
        let text = `Ditemukan ${total} data`;
        if (filters.search) text += ` untuk nama toko "${filters.search}"`;
        if (filters.kategori) text += ` dengan kategori "${filters.kategori}"`;
        if (filters.kecamatan) text += ` di kecamatan "${filters.kecamatan}"`;
        if (filters.desa) text += ` di desa "${filters.desa}"`;
        
        document.getElementById('searchResultText').textContent = text;
        searchInfo.style.display = 'block';
        searchInfo.classList.add('fade-in');
    }

    // Function untuk clear errors
    function clearErrors() {
        document.querySelectorAll('.error-message').forEach(el => {
            el.textContent = '';
            el.style.display = 'none';
        });
        document.querySelectorAll('.form-group input, .form-group select').forEach(el => {
            el.style.borderColor = '#ced4da';
        });
    }

    // Function untuk show field error
    function showFieldError(field, message) {
        const errorEl = document.getElementById(`error-${field}`);
        const inputEl = document.querySelector(`[name="${field}"]`);
        
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.style.display = 'block';
            errorEl.style.color = '#dc3545';
        }
        
        if (inputEl) {
            inputEl.style.borderColor = '#dc3545';
        }
    }

    // Main AJAX search function
    function performSearch(formData, page = 1) {
        showLoading();
        clearErrors();
        alertContainer.innerHTML = '';

        // Add page parameter
        formData.append('page', page);

        fetch('{{ route("umkm.post") }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(async response => {
            const text = await response.text();
            console.log("RAW RESPONSE:", text);
            const data = JSON.parse(text); // parsing manual agar bisa debug
            hideLoading();
        })
        .then(data => {
            hideLoading();
            
            if (data.success) {
                // Update table content
                tableBody.innerHTML = data.html;
                
                // Update pagination
                paginationContainer.innerHTML = data.pagination;
                
                // Update search info
                updateSearchInfo(data.filters, data.total);
                
                // Update URL without refresh
                const url = new URL(window.location);
                Object.keys(data.filters).forEach(key => {
                    if (data.filters[key]) {
                        url.searchParams.set(key, data.filters[key]);
                    } else {
                        url.searchParams.delete(key);
                    }
                });
                window.history.pushState({}, '', url);
                
                // Add fade-in animation
                setTimeout(() => {
                    document.querySelectorAll('.fade-in').forEach(el => {
                        el.style.opacity = '1';
                        el.style.transform = 'translateY(0)';
                    });
                }, 100);
                
            } else {
                showAlert('Terjadi kesalahan saat mencari data');
            }
        })
        .catch(error => {
            hideLoading();
            
            if (error.response && error.response.status === 422) {
                // Validation errors
                error.response.json().then(errorData => {
                    Object.keys(errorData.errors).forEach(field => {
                        showFieldError(field, errorData.errors[field][0]);
                    });
                });
            } else {
                showAlert('Terjadi kesalahan jaringan. Silakan coba lagi.');
            }
            
            console.error('Search error:', error);
        });
    }

    // Form submit handler
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const nama = formData.get('search')?.trim();
        
        // Client-side validation
        if (!nama) {
            showFieldError('search', 'Nama toko harus diisi untuk melakukan pencarian!');
            document.getElementById('filterNama').focus();
            return;
        }
        
        performSearch(formData);
    });

    // Reset button handler
    resetBtn.addEventListener('click', function() {
        form.reset();
        clearErrors();
        searchInfo.style.display = 'none';
        alertContainer.innerHTML = '';
        
        // Load initial data
        showLoading();
        fetch('{{ route("umkm.loadData") }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            tableBody.innerHTML = data.html;
            paginationContainer.innerHTML = data.pagination;
            
            // Clear URL parameters
            window.history.pushState({}, '', '{{ route("umkm") }}');
        })
        .catch(error => {
            hideLoading();
            console.error('Reset error:', error);
        });
    });

    // Pagination click handler (Event delegation)
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('page-link') && e.target.hasAttribute('data-page')) {
            e.preventDefault();
            
            const page = e.target.getAttribute('data-page');
            const formData = new FormData(form);
            
            // Only search if nama is filled
            if (formData.get('search')?.trim()) {
                performSearch(formData, page);
            }
        }
    });

    // Real-time validation for nama toko
    document.getElementById('filterNama').addEventListener('input', function() {
        const value = this.value.trim();
        clearErrors();
        
        if (value) {
            this.style.borderColor = '#28a745';
        } else {
            this.style.borderColor = '#ced4da';
        }
    });

    // Auto-search on Enter key (optional)
    document.querySelectorAll('#searchForm input').forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                form.dispatchEvent(new Event('submit'));
            }
        });
    });
});