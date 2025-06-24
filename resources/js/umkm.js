// Data store (dalam aplikasi nyata, ini akan dari database)
let tokoData = [
    // Sample data untuk demonstrasi
    {id: 1, nama: "Warung Bu Sari", alamat: "Jl. Sudirman No. 123", koordinat: "-6.2088, 106.8456", kategori: "", kecamatan: "Menteng", desa: "Kebon Sirih"},
    {id: 2, nama: "Toko Elektronik Prima", alamat: "Jl. Thamrin No. 45", koordinat: "-6.1944, 106.8229", kategori: "", kecamatan: "Tanah Abang", desa: "Bendungan Hilir"},
    {id: 3, nama: "Kedai Kopi Nusantara", alamat: "Jl. Kemang Raya No. 67", koordinat: "-6.2615, 106.8106", kategori: "", kecamatan: "Mampang Prapatan", desa: "Kemang"},
    {id: 4, nama: "Fashion Store Trendy", alamat: "Jl. Cihampelas No. 89", koordinat: "-6.8915, 107.6107", kategori: "Fashion", kecamatan: "Coblong", desa: "Cipaganti"},
    {id: 5, nama: "Apotek Sehat Sentosa", alamat: "Jl. Asia Afrika No. 12", koordinat: "-6.9147, 107.6098", kategori: "", kecamatan: "Sumur Bandung", desa: "Braga"}
];

// Generate lebih banyak sample data
const namaTemplate = ["Warung", "Toko", "Kedai", "Rumah Makan", "Apotek", "Kios", "Depot"];
const kategoriTemplate = ["", "Makanan", "Minuman", "Elektronik", "Fashion", "Kesehatan", "Lainnya"];
const kecamatanTemplate = ["Menteng", "Tanah Abang", "Mampang Prapatan", "Coblong", "Sumur Bandung", "Setiabudi", "Kebayoran Baru"];
const desaTemplate = ["Kebon Sirih", "Bendungan Hilir", "Kemang", "Cipaganti", "Braga", "Kuningan", "Senayan"];

// Generate random data untuk simulasi 55k records
for(let i = 6; i <= 100; i++) {
    const namaRandom = namaTemplate[Math.floor(Math.random() * namaTemplate.length)];
    const kategoriRandom = kategoriTemplate[Math.floor(Math.random() * kategoriTemplate.length)];
    const kecamatanRandom = kecamatanTemplate[Math.floor(Math.random() * kecamatanTemplate.length)];
    const desaRandom = desaTemplate[Math.floor(Math.random() * desaTemplate.length)];
    
    tokoData.push({
        id: i,
        nama: `${namaRandom} ${String.fromCharCode(65 + Math.floor(Math.random() * 26))}${Math.floor(Math.random() * 100)}`,
        alamat: `Jl. Sample No. ${Math.floor(Math.random() * 999)}`,
        koordinat: `${(-6 - Math.random()).toFixed(4)}, ${(106 + Math.random()).toFixed(4)}`,
        kategori: Math.random() > 0.6 ? kategoriRandom : "", // 40% chance tidak ada kategori
        kecamatan: kecamatanRandom,
        desa: desaRandom
    });
}

let filteredData = [...tokoData];
let currentPage = 1;
const itemsPerPage = 10;
let isAuthenticated = false;

// Keywords untuk auto kategorisasi
const categoryKeywords = {
    "Makanan": ["warung", "warteg", "rumah makan", "resto", "restaurant", "nasi", "bakso", "soto", "gudeg", "padang"],
    "Minuman": ["kopi", "teh", "jus", "minuman", "cafe", "kedai kopi", "bubble tea", "es", "fresh", "drink"],
    "Elektronik": ["hp", "laptop", "elektronik", "gadget", "komputer", "handphone", "smartphone", "tablet", "TV", "radio"],
    "Fashion": ["baju", "sepatu", "tas", "fashion", "clothing", "butik", "distro", "kaos", "celana", "jaket"],
    "Kesehatan": ["apotek", "obat", "kesehatan", "farmasi", "klinik", "dokter", "vitamin", "medical", "sehat"]
};

function showSection(sectionName) {
    // Hide all sections
    document.querySelectorAll('.section').forEach(section => {
        section.classList.remove('active');
    });
    
    // Remove active class from all nav buttons
    document.querySelectorAll('.nav-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected section
    document.getElementById(sectionName).classList.add('active');
    
    // Add active class to clicked button
    event.target.classList.add('active');
    
    // Update data when switching to relevant sections
    if (sectionName === 'search') {
        renderTable();
    } else if (sectionName === 'stats') {
        updateStats();
    } else if (sectionName === 'categorize') {
        updateCategorizationInfo();
    }
}

function renderTable() {
    const tbody = document.getElementById('tableBody');
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const pageData = filteredData.slice(startIndex, endIndex);
    
    tbody.innerHTML = '';
    
    pageData.forEach((toko, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${startIndex + index + 1}</td>
            <td>${toko.nama}</td>
            <td>${toko.alamat}</td>
            <td>${toko.koordinat}</td>
            <td>${toko.kategori || '<span style="color: #dc3545;">Tidak ada</span>'}</td>
            <td>${toko.kecamatan}</td>
            <td>${toko.desa}</td>
            <td>
                <button class="btn" style="padding: 5px 10px; font-size: 12px;" onclick="editToko(${toko.id})">Edit</button>
            </td>
        `;
        tbody.appendChild(row);
    });
    
    renderPagination();
}

function renderPagination() {
    const pagination = document.getElementById('pagination');
    const totalPages = Math.ceil(filteredData.length / itemsPerPage);
    
    pagination.innerHTML = '';
    
    // Previous button
    if (currentPage > 1) {
        const prevBtn = document.createElement('button');
        prevBtn.textContent = '« Prev';
        prevBtn.onclick = () => {
            currentPage--;
            renderTable();
        };
        pagination.appendChild(prevBtn);
    }
    
    // Page numbers
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);
    
    for (let i = startPage; i <= endPage; i++) {
        const pageBtn = document.createElement('button');
        pageBtn.textContent = i;
        pageBtn.onclick = () => {
            currentPage = i;
            renderTable();
        };
        if (i === currentPage) {
            pageBtn.classList.add('active');
        }
        pagination.appendChild(pageBtn);
    }
    
    // Next button
    if (currentPage < totalPages) {
        const nextBtn = document.createElement('button');
        nextBtn.textContent = 'Next »';
        nextBtn.onclick = () => {
            currentPage++;
            renderTable();
        };
        pagination.appendChild(nextBtn);
    }
}

function applyFilters() {
    const filterNama = document.getElementById('filterNama').value.toLowerCase();
    const filterKategori = document.getElementById('filterKategori').value;
    const filterKecamatan = document.getElementById('filterKecamatan').value.toLowerCase();
    const filterDesa = document.getElementById('filterDesa').value.toLowerCase();
    
    filteredData = tokoData.filter(toko => {
        return (
            (!filterNama || toko.nama.toLowerCase().includes(filterNama)) &&
            (!filterKategori || (filterKategori === "" ? !toko.kategori : toko.kategori === filterKategori)) &&
            (!filterKecamatan || toko.kecamatan.toLowerCase().includes(filterKecamatan)) &&
            (!filterDesa || toko.desa.toLowerCase().includes(filterDesa))
        );
    });
    
    currentPage = 1;
    renderTable();
}

function clearFilters() {
    document.getElementById('filterNama').value = '';
    document.getElementById('filterKategori').value = '';
    document.getElementById('filterKecamatan').value = '';
    document.getElementById('filterDesa').value = '';
    
    filteredData = [...tokoData];
    currentPage = 1;
    renderTable();
}

function login() {
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    
    // Simple authentication (dalam aplikasi nyata, gunakan sistem autentikasi yang proper)
    if (username === 'admin' && password === '123456') {
        isAuthenticated = true;
        document.getElementById('loginSection').style.display = 'none';
        document.getElementById('insertSection').style.display = 'block';
    } else {
        alert('Username atau password salah!');
    }
}

function logout() {
    isAuthenticated = false;
    document.getElementById('loginSection').style.display = 'block';
    document.getElementById('insertSection').style.display = 'none';
    document.getElementById('username').value = '';
    document.getElementById('password').value = '';
}

function insertData() {
    if (!isAuthenticated) {
        alert('Anda harus login terlebih dahulu!');
        return;
    }
    
    const nama = document.getElementById('newNama').value.trim();
    const alamat = document.getElementById('newAlamat').value.trim();
    const koordinat = document.getElementById('newKoordinat').value.trim();
    const kategori = document.getElementById('newKategori').value;
    const kecamatan = document.getElementById('newKecamatan').value.trim();
    const desa = document.getElementById('newDesa').value.trim();
    
    // Validasi input
    if (!nama || !alamat || !koordinat || !kecamatan || !desa) {
        showAlert('insertAlert', 'Semua field harus diisi!', 'error');
        return;
    }
    
    // Validasi format koordinat
    const koordinatRegex = /^-?\d+\.?\d*,\s*-?\d+\.?\d*$/;
    if (!koordinatRegex.test(koordinat)) {
        showAlert('insertAlert', 'Format koordinat tidak valid! Contoh: -6.2088, 106.8456', 'error');
        return;
    }
    
    // Tambah data baru
    const newId = Math.max(...tokoData.map(t => t.id)) + 1;
    const newToko = {
        id: newId,
        nama: nama,
        alamat: alamat,
        koordinat: koordinat,
        kategori: kategori,
        kecamatan: kecamatan,
        desa: desa
    };
    
    tokoData.push(newToko);
    filteredData = [...tokoData];
    
    // Reset form
    document.getElementById('newNama').value = '';
    document.getElementById('newAlamat').value = '';
    document.getElementById('newKoordinat').value = '';
    document.getElementById('newKategori').value = '';
    document.getElementById('newKecamatan').value = '';
    document.getElementById('newDesa').value = '';
    
    showAlert('insertAlert', 'Data berhasil ditambahkan!', 'success');
}

function showAlert(containerId, message, type) {
    const container = document.getElementById(containerId);
    container.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
    
    setTimeout(() => {
        container.innerHTML = '';
    }, 3000);
}

function editToko(id) {
    const toko = tokoData.find(t => t.id === id);
    if (toko) {
        const newNama = prompt('Nama Toko:', toko.nama);
        if (newNama !== null && newNama.trim() !== '') {
            toko.nama = newNama.trim();
            
            const newKategori = prompt('Kategori (Makanan/Minuman/Elektronik/Fashion/Kesehatan/Lainnya):', toko.kategori);
            if (newKategori !== null) {
                toko.kategori = newKategori.trim();
            }
            
            filteredData = [...tokoData];
            renderTable();
            alert('Data berhasil diupdate!');
        }
    }
}

function startCategorization() {
    const uncategorizedTokos = tokoData.filter(toko => !toko.kategori);
    
    if (uncategorizedTokos.length === 0) {
        alert('Semua toko sudah memiliki kategori!');
        return;
    }
    
    let processed = 0;
    let categorized = 0;
    const total = uncategorizedTokos.length;
    
    const progressFill = document.getElementById('progressFill');
    const progressText = document.getElementById('progressText');
    const resultsDiv = document.getElementById('categorizationResults');
    
    resultsDiv.innerHTML = '<h4>Hasil Kategorisasi:</h4>';
    
    // Simulasi proses kategorisasi dengan delay
    const processInterval = setInterval(() => {
        if (processed >= total) {
            clearInterval(processInterval);
            progressText.textContent = `Selesai! ${categorized} dari ${total} toko berhasil dikategorikan`;
            filteredData = [...tokoData];
            return;
        }
        
        const toko = uncategorizedTokos[processed];
        const namaLower = toko.nama.toLowerCase();
        let foundCategory = null;
        
        // Cek setiap kategori dan keyword-nya
        for (const [category, keywords] of Object.entries(categoryKeywords)) {
            for (const keyword of keywords) {
                if (namaLower.includes(keyword)) {
                    foundCategory = category;
                    break;
                }
            }
            if (foundCategory) break;
        }
        
        if (foundCategory) {
            toko.kategori = foundCategory;
            categorized++;
            
            const resultItem = document.createElement('div');
            resultItem.style.cssText = 'padding: 10px; margin: 5px 0; background: #d4edda; border-radius: 5px; border-left: 4px solid #28a745;';
            resultItem.innerHTML = `<strong>${toko.nama}</strong> → ${foundCategory}`;
            resultsDiv.appendChild(resultItem);
        } else {
            const resultItem = document.createElement('div');
            resultItem.style.cssText = 'padding: 10px; margin: 5px 0; background: #f8d7da; border-radius: 5px; border-left: 4px solid #dc3545;';
            resultItem.innerHTML = `<strong>${toko.nama}</strong> → Tidak dapat dikategorikan`;
            resultsDiv.appendChild(resultItem);
        }
        
        processed++;
        const percentage = (processed / total) * 100;
        progressFill.style.width = percentage + '%';
        progressText.textContent = `Memproses ${processed}/${total} toko...`;
        
        // Auto scroll ke hasil terbaru
        resultsDiv.scrollTop = resultsDiv.scrollHeight;
        
    }, 100); // Proses setiap 100ms untuk efek visual
}

function updateCategorizationInfo() {
    const uncategorizedCount = tokoData.filter(toko => !toko.kategori).length;
    document.getElementById('uncategorizedCount').textContent = uncategorizedCount;
    
    const progressFill = document.getElementById('progressFill');
    const progressText = document.getElementById('progressText');
    
    progressFill.style.width = '0%';
    progressText.textContent = 'Siap untuk memulai kategorisasi';
}

function updateStats() {
    const totalToko = tokoData.length;
    const kategoris = [...new Set(tokoData.filter(t => t.kategori).map(t => t.kategori))];
    const kecamatans = [...new Set(tokoData.map(t => t.kecamatan))];
    const desas = [...new Set(tokoData.map(t => t.desa))];
    
    document.getElementById('totalToko').textContent = totalToko;
    document.getElementById('totalKategori').textContent = kategoris.length;
    document.getElementById('totalKecamatan').textContent = kecamatans.length;
    document.getElementById('totalDesa').textContent = desas.length;
    
    // Update distribusi kategori
    const categoryStats = document.getElementById('categoryStats');
    const categoryCount = {};
    
    tokoData.forEach(toko => {
        const cat = toko.kategori || 'Tidak Ada Kategori';
        categoryCount[cat] = (categoryCount[cat] || 0) + 1;
    });
    
    let statsHTML = '';
    Object.entries(categoryCount).forEach(([category, count]) => {
        const percentage = ((count / totalToko) * 100).toFixed(1);
        statsHTML += `
            <div style="margin-bottom: 15px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                    <span><strong>${category}</strong></span>
                    <span>${count} toko (${percentage}%)</span>
                </div>
                <div style="background: #e9ecef; border-radius: 10px; height: 20px; overflow: hidden;">
                    <div style="background: linear-gradient(45deg, #4ECDC4, #44A08D); height: 100%; width: ${percentage}%; transition: width 0.3s ease;"></div>
                </div>
            </div>
        `;
    });
    
    categoryStats.innerHTML = statsHTML;
}

// Event listeners untuk real-time filtering
document.getElementById('filterNama').addEventListener('input', applyFilters);
document.getElementById('filterKategori').addEventListener('change', applyFilters);
document.getElementById('filterKecamatan').addEventListener('input', applyFilters);
document.getElementById('filterDesa').addEventListener('input', applyFilters);

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 'f') {
        e.preventDefault();
        document.getElementById('filterNama').focus();
    }
});

// Initialize
window.onload = function() {
    renderTable();
    updateStats();
    updateCategorizationInfo();
};