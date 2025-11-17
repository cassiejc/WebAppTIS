<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Petelur</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        .form-container { margin-left: 20px; }
        .page-title { margin-left: 10px; }
        .custom-dropdown { position: relative; }
        .dropdown-toggle {
            background-color: white; color: #333; border: 1px solid #dee2e6; cursor: pointer;
            text-align: left; display: flex; justify-content: space-between; align-items: center;
        }
        .dropdown-toggle:hover, .dropdown-toggle:focus { background-color: #f8f9fa; border-color: #0d6efd; }
        .dropdown-toggle:disabled, .dropdown-toggle.disabled { background-color: #e9ecef; color: #6c757d; cursor: not-allowed; border-color: #dee2e6; }
        .dropdown-toggle::after { content: "▼"; font-size: 12px; }
        .farm-search-input { border: none; border-bottom: 1px solid #dee2e6; background-color: #f8f9fa; }
        .farm-search-input:focus { outline: 2px solid #0d6efd; background-color: white; }
        .dropdown-content {
            display: none; position: absolute; background-color: white; min-width: 100%;
            max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6;
            border-radius: 0 0 0.375rem 0.375rem; border-top: none; z-index: 1000;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
        }
        .dropdown-content .farm-option {
            color: #333; padding: 10px 12px; text-decoration: none;
            display: block; cursor: pointer; border-bottom: 1px solid #eee;
        }
        .dropdown-content .farm-option:hover { background-color: #f8f9fa; }
        .dropdown-content .farm-option:last-child { border-bottom: none; }
        .show { display: block; }
        .integer-input, .currency-input, .varchar-input, .letters-only-input { position: relative; max-width: 400px; }
        .currency-prefix { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #666; pointer-events: none; }
        .currency-input input { padding-left: 35px; }
        .loading { display: none; color: #666; font-style: italic; }
        .form-check { padding-left: 2.5em; }
        .form-check-input { float: left; margin-left: -2.5em; }
        
        textarea::-webkit-scrollbar { display: none; }
        textarea { resize: none !important; }

        /* Style untuk pesan error per field */
        .field-error-msg {
            font-size: 0.875em;
            margin-top: 0.25rem;
        }
        /* Style untuk border merah pada input yang error */
        .is-invalid {
            border-color: #dc3545 !important;
        }
        
        /* Style untuk field yang auto-calculated */
        .auto-calculated {
            background-color: #f8f9fa !important;
            cursor: not-allowed;
        }
        
        .auto-calc-info {
            font-size: 0.875em;
            color: #6c757d;
            margin-top: 0.25rem;
            font-style: italic;
        }

        /* BARU: Style untuk info kapasitas */
        .farm-capacity-info {
            color: #0d6efd;
            font-weight: bold;
        }
        /* BARU: Style untuk tombol refresh */
        #refreshKapasitasBtn {
            display: none; /* Sembunyi by default */
            padding: 0.1rem 0.4rem; 
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <h2 class="page-title mb-4">
            <span id="dynamic-title">Petelur</span> - <?=$nama_lokasi_header?>
        </h2>

        <form method="post" action="" id="petelurForm" class="form-container" novalidate>
            <input type="hidden" id="selected_tipe_ternak" name="tipe_ternak" value="">
            
            <div id="questions-container">
                <?php if (!empty($questions)): ?>
                    <?php
                    $tipe_ternak_q = null;
                    foreach ($questions as $q) {
                        if ($q['field_name'] === 'tipe_ternak') {
                            $tipe_ternak_q = $q;
                            break;
                        }
                    }
                    if ($tipe_ternak_q):
                        $q = $tipe_ternak_q;
                    ?>
                        <div class="mb-4 question-group" data-field="<?= htmlspecialchars($q['field_name'], ENT_QUOTES) ?>">
                            <label class="form-label fw-bold">
                                <?= htmlspecialchars($q['question_text']) ?>
                                <?php if (!empty($q['required'])): ?><span class="text-danger">*</span><?php endif; ?>
                            </label>
                            <select name="q<?= $q['questions_id'] ?>" class="form-select" style="max-width: 400px;" onchange="changeTipeTermak(this.value)" <?= !empty($q['required']) ? 'required' : '' ?>>
                                <option value="">-- Pilih --</option>
                                <?php foreach ($q['options'] as $opt): ?>
                                    <option value="<?= htmlspecialchars($opt['option_text'], ENT_QUOTES) ?>"><?= htmlspecialchars($opt['option_text']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <div class="loading" id="loading">Loading...</div>
            
            <button type="submit" class="btn btn-primary px-4 py-2 mt-4" id="submit-btn">Submit</button>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    
    // **BARU: Variabel global**
    let currentSelectedKapasitas = 0;
    let ageInterval = null;

    // --- FUNGSI HELPER AWAL (TIDAK BERUBAH) ---
    function isLetterKey(evt) { const c = evt.keyCode; return (c >= 65 && c <= 90) || (c >= 97 && c <= 122) || c === 32 || c === 8 || (c >= 37 && c <= 40); }
    function isIntegerKey(evt) { const c = evt.keyCode; return (c >= 48 && c <= 57) || c === 8 || (c >= 37 && c <= 40); }
    function isNumberKey(evt) { 
        const c = evt.keyCode; 
        const input = evt.target;
        if (c === 8 || c === 9 || c === 27 || c === 13 || (c >= 37 && c <= 40)) { return true; }
        if (c >= 48 && c <= 57) { return true; }
        // Perbolehkan satu titik desimal
        if (c === 46 || c === 190) { return input.value.indexOf('.') === -1; } 
        return false;
    }
    function isAlphanumericKey(evt) { 
        const c = evt.keyCode; 
        return (c >= 48 && c <= 57) || (c >= 65 && c <= 90) || (c >= 97 && c <= 122) || c === 32 || c === 8 || (c >= 37 && c <= 40) || c === 45 || c === 95;
    }
    function formatWithComma(input) { 
        let v = input.value.replace(/[^\d]/g, ''); 
        if (v) {
            input.value = parseInt(v, 10).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        } else {
            input.value = '';
        }
    }
    function shouldBeIntegerType(f) { return ['efektif_terisi_cp_petelur', 'efektif_terisi_non_cp_petelur', 'doa_woa_petelur', 'layer_pakai_pakan_cp', 'layer_selain_pakan_cp', 'layer_jumlah_kandang', 'layer_lama_puncak_produksi', 'layer_populasi', 'layer_woa', 'layer_umur_tertua', 'layer_umur_termuda', 'petelur_umur'].includes(f); }    function shouldBeCurrencyType(f) { return ['harga_jual_telur_terakhir', 'layer_harga_jual_telur', 'layer_harga_beli_jagung', 'layer_harga_beli_katul', 'layer_harga_afkir'].includes(f); }
    function shouldHaveDecimalSupport(f) { return ['deplesi_petelur', 'intake_petelur', 'produksi_telur_petelur', 'berat_telur_petelur', 'fcr_petelur', 'layer_hen_day', 'layer_deplesi', 'layer_intake', 'layer_produksi_telur', 'layer_berat_telur', 'layer_fcr', 'suhu_kandang_layer', 'kelembapan_kandang_layer'].includes(f); }
    function shouldBeVarcharType(f) { return ['layer_kode_label_pakan', 'layer_nama_kandang', 'petelur_kode_label_pakan'].includes(f); }
    function autoResize(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    }
    function toggleDropdown(qId) { const dd = document.getElementById(`dd_content_${qId}`); if (dd) dd.classList.toggle('show'); }
    function filterOptions(qId) { const filter = document.getElementById(`dd_search_${qId}`).value.toUpperCase(); document.querySelectorAll(`#dd_content_${qId} .farm-option`).forEach(opt => { opt.style.display = opt.textContent.toUpperCase().includes(filter) ? '' : 'none'; }); }

    
    // --- FUNGSI DROPDOWN (DIPERBARUI UNTUK KAPASITAS) ---
    function selectOption(qId, text, element) { // **element ditambahkan**
        const hiddenInput = document.getElementById(`dd_hidden_${qId}`);
        const button = document.getElementById(`dd_btn_${qId}`);
        const dropdown = document.getElementById(`dd_content_${qId}`);
        
        if (hiddenInput) hiddenInput.value = text;
        if (button) button.querySelector('span').textContent = text;
        if (dropdown) dropdown.classList.remove('show');

        // Hapus pesan error saat opsi dipilih
        const container = button?.closest('.question-group');
        if (container) {
            const errorMsg = container.querySelector('.field-error-msg');
            if (errorMsg) errorMsg.remove();
            button.classList.remove('is-invalid');
        }
        
        // **BARU: Simpan dan tampilkan kapasitas**
        const kapasitas = element.dataset.kapasitas;
        currentSelectedKapasitas = 0; // Reset
        const kap_int = parseInt(kapasitas, 10);

        // Update caption. Kita gunakan satu ID universal.
        const capacityDiv = document.getElementById('kapasitas_caption_div');

        if (capacityDiv) {
            if (!isNaN(kap_int)) {
                currentSelectedKapasitas = kap_int;
                const formatted = kap_int.toLocaleString('en-US');
                capacityDiv.textContent = `Kapasitas farm: ${formatted}`;
            } else {
                capacityDiv.textContent = 'Kapasitas farm: -';
            }
        }
    }

    // --- FUNGSI PERHITUNGAN UMUR (DIPERBARUI) ---
    
    function calculatePetelurAge() {
        const chickInInput = document.querySelector('input[data-field="tanggal_chick_in_petelur"]');
        const ageInput = document.querySelector('input[data-field="petelur_umur"]');
        
        if (!chickInInput || !ageInput) {
            return; // Keluar jika field tidak ada (belum dimuat)
        }
        
        const chickInDate = chickInInput.value;
        
        if (!chickInDate) {
            ageInput.value = '';
            return;
        }
        
        try {
            const startDate = new Date(chickInDate);
            const today = new Date();
            
            startDate.setHours(0, 0, 0, 0);
            today.setHours(0, 0, 0, 0);
            
            const timeDifference = today.getTime() - startDate.getTime();
            const daysDifference = Math.floor(timeDifference / (1000 * 3600 * 24));
            
            if (daysDifference >= 0) {
                ageInput.value = daysDifference;
            } else {
                ageInput.value = 0;
            }
        } catch (error) {
            console.error('Error calculating petelur age:', error);
            ageInput.value = '';
        }
    }
    
    // **PERBAIKAN: Fungsi ini sekarang me-manage interval**
    function startPetelurAgeCalculation() {
        if (ageInterval) clearInterval(ageInterval); // Hentikan interval lama
        calculatePetelurAge(); // Hitung sekali saat dimuat
        ageInterval = setInterval(calculatePetelurAge, 60000); // Mulai interval baru
    }
    
    function attachChickInListener() {
        const chickInInput = document.querySelector('input[data-field="tanggal_chick_in_petelur"]');
        if (chickInInput) {
            chickInInput.removeEventListener('change', calculatePetelurAge); // Hapus listener lama
            chickInInput.addEventListener('change', calculatePetelurAge); // Tambah listener baru
        }
    }

    // --- FUNGSI BARU UNTUK REFRESH DATA ---
    
    /**
     * Helper untuk mereset pilihan farm dan kapasitas
     */
    function resetSelectedFarm() {
        // Cari dropdown 'nama_farm' atau 'layer_nama_farm'
        let farmDropdownGroup = document.querySelector('.question-group[data-field="nama_farm"]');
        if (!farmDropdownGroup) farmDropdownGroup = document.querySelector('.question-group[data-field="layer_nama_farm"]');
        if (!farmDropdownGroup) return; // Tidak ada dropdown farm di halaman ini
        
        const questionId = farmDropdownGroup.dataset.questionId;
        const hiddenInput = document.getElementById(`dd_hidden_${questionId}`);
        const button = document.getElementById(`dd_btn_${questionId}`);
        const selectedText = button ? button.querySelector('span') : null;
        
        if (hiddenInput) hiddenInput.value = '';
        if (selectedText) selectedText.textContent = '-- Pilih --'; // Reset ke default
        if (button) button.classList.remove('selected-farm');
        
        // Reset juga caption kapasitas
        const capacityDiv = document.getElementById('kapasitas_caption_div');
        if (capacityDiv) {
            capacityDiv.textContent = 'Kapasitas farm: -';
        }
        currentSelectedKapasitas = 0;
    }

    /**
     * Fungsi utama untuk refresh data farm via AJAX
     */
    function refreshFarmData(button) {
        const tipeTernak = document.getElementById('selected_tipe_ternak').value;
        if (!tipeTernak) return; // Seharusnya tidak terjadi jika tombol terlihat

        // Cari dropdown 'nama_farm' atau 'layer_nama_farm'
        let farmDropdownGroup = document.querySelector('.question-group[data-field="nama_farm"]');
        if (!farmDropdownGroup) farmDropdownGroup = document.querySelector('.question-group[data-field="layer_nama_farm"]');
        if (!farmDropdownGroup) return;

        const questionId = farmDropdownGroup.dataset.questionId;
        const dropdownContent = document.getElementById(`dd_content_${questionId}`);
        
        // Tampilkan loading
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
        const oldHtml = dropdownContent.innerHTML; 
        dropdownContent.innerHTML = '<div class="farm-option text-muted text-center py-2">Memuat data baru...</div>';

        const xhr = new XMLHttpRequest();
        // Panggil endpoint baru di controller Petelur
        xhr.open('POST', "<?=site_url('Visiting_Petelur_Controller/ajax_refresh_farm_options')?>", true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                button.disabled = false;
                button.innerHTML = '<i class="bi bi-arrow-clockwise"></i>';
                
                if (xhr.status === 200) {
                    try {
                        const newOptions = JSON.parse(xhr.responseText);
                        
                        dropdownContent.innerHTML = ''; // Bersihkan
                        
                        // Tambahkan input search lagi
                        const searchInput = document.createElement('input');
                        searchInput.type = 'text';
                        searchInput.placeholder = 'Cari...';
                        searchInput.id = `dd_search_${questionId}`;
                        searchInput.className = 'form-control farm-search-input';
                        searchInput.onkeyup = () => filterOptions(questionId);
                        dropdownContent.appendChild(searchInput);

                        // Bangun ulang opsi-opsi
                        if (newOptions && newOptions.length > 0) {
                            newOptions.forEach(o => {
                                const kapasitas = (o.kapasitas_farm !== null) ? o.kapasitas_farm : '0';
                                const escapedText = o.option_text.replace(/'/g, "\\'");
                                const optionDiv = document.createElement('div');
                                optionDiv.className = 'farm-option option-item';
                                optionDiv.dataset.value = o.option_text;
                                optionDiv.dataset.tipe = o.tipe_ternak || '';
                                optionDiv.dataset.kapasitas = kapasitas;
                                optionDiv.onclick = () => selectOption(questionId, escapedText, optionDiv);
                                optionDiv.textContent = o.option_text;
                                dropdownContent.appendChild(optionDiv);
                            });
                        } else {
                            const noOptionDiv = document.createElement('div');
                            noOptionDiv.className = 'farm-option text-muted text-center py-2';
                            noOptionDiv.textContent = 'Tidak ada opsi tersedia';
                            dropdownContent.appendChild(noOptionDiv);
                        }

                        // Reset pilihan farm saat ini
                        resetSelectedFarm();
                        Swal.fire('Sukses', 'Data farm berhasil diperbarui.', 'success');

                    } catch (e) {
                        console.error('Gagal parse JSON:', e);
                        Swal.fire('Error', 'Gagal memproses data baru.', 'error');
                        dropdownContent.innerHTML = oldHtml;
                    }
                } else {
                    Swal.fire('Error', 'Gagal mengambil data farm dari server.', 'error');
                    dropdownContent.innerHTML = oldHtml;
                }
            }
        };
        
        xhr.send('tipe_ternak=' + encodeURIComponent(tipeTernak));
    }

    // --- LOGIKA UTAMA FORM DINAMIS (DIPERBARUI) ---
    function changeTipeTermak(tipeTermak) {
        document.getElementById('dynamic-title').textContent = tipeTermak || 'Petelur';
        document.getElementById('selected_tipe_ternak').value = tipeTermak;
        const container = document.getElementById('questions-container');
        
        // **PERBAIKAN: Hentikan interval umur**
        if (ageInterval) clearInterval(ageInterval);
        ageInterval = null;
        currentSelectedKapasitas = 0; // Reset kapasitas
        
        container.querySelectorAll('.question-group').forEach(g => { if (g.dataset.field !== 'tipe_ternak') g.remove(); });
        
        if (!tipeTermak) { 
            enableDependentElements(false); 
            return; 
        }
        
        document.getElementById('loading').style.display = 'block';
        document.getElementById('submit-btn').disabled = true;
        
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '<?= site_url('Visiting_Petelur_Controller/get_questions_by_type') ?>', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    updateQuestionsDisplay(JSON.parse(xhr.responseText));
                } catch (e) {
                    console.error("Gagal parse JSON:", e, xhr.responseText);
                    container.innerHTML += '<p class="text-danger">Gagal memuat pertanyaan.</p>';
                }
            } else {
                container.innerHTML += '<p class="text-danger">Gagal menghubungi server.</p>';
            }
            document.getElementById('loading').style.display = 'none';
            document.getElementById('submit-btn').disabled = false;
        };
        xhr.send('tipe_ternak=' + encodeURIComponent(tipeTermak));
    }
    
    // **PERBAIKAN: Panggil validasi dan kalkulasi umur SETELAH elemen dibuat**
    function updateQuestionsDisplay(questions) {
        const container = document.getElementById('questions-container');
        questions.filter(q => q.field_name !== 'tipe_ternak').forEach(q => {
            const el = createQuestionElement(q);
            if(el) container.appendChild(el);
        });
        
        // Pindahkan fungsi-fungsi ini ke sini
        enableDependentElements(true);
        addRealTimeValidation();
        attachChickInListener();
        startPetelurAgeCalculation(); // Mulai kalkulasi umur
    }

    // --- FUNGSI PEMBUAT ELEMEN (DIPERBARUI) ---
    function createQuestionElement(q) {
        const formGroup = document.createElement('div');
        formGroup.className = 'mb-4 question-group';
        formGroup.dataset.field = q.field_name;
        formGroup.dataset.questionId = q.questions_id; // Simpan ID

        const isRequired = q.required && q.required !== '0' && q.required !== 0;
        formGroup.innerHTML = `<label class="form-label fw-bold">${q.question_text} ${isRequired ? '<span class="text-danger">*</span>' : ''}</label>`;
        const commonAttrs = `name="q${q.questions_id}" data-field="${q.field_name}" class="form-control" ${isRequired ? 'required' : ''} style="max-width:400px;"`;

        if (q.type === 'select' && q.options) {
            const isSearchable = q.field_name === 'nama_farm' || q.field_name === 'layer_nama_farm'; // **Lebih spesifik**
            if (isSearchable) {
            
                const optionsHTML = q.options.map(opt => {
                    // **MODIFIKASI: Tambahkan data-kapasitas**
                    const kapasitas = (opt.kapasitas_farm !== null && opt.kapasitas_farm !== undefined) ? opt.kapasitas_farm : '0';
                    const escapedText = opt.option_text.replace(/'/g, "\\'");
                    // **PENTING: kirim 'this' (elemen) ke selectOption**
                    return `<div class="farm-option" data-value="${escapedText}" data-kapasitas="${kapasitas}" data-tipe="${opt.tipe_ternak || ''}" onclick="selectOption(${q.questions_id}, '${escapedText}', this)">${opt.option_text}</div>`;
                }).join('');

                formGroup.innerHTML += `
                    <div class="custom-dropdown" style="max-width: 400px;">
                        <input type="hidden" name="q${q.questions_id}" id="dd_hidden_${q.questions_id}" ${isRequired ? 'required' : ''} data-field="${q.field_name}">
                        <button type="button" class="btn dropdown-toggle w-100" id="dd_btn_${q.questions_id}" onclick="toggleDropdown(${q.questions_id})"><span>-- Pilih --</span></button>
                        <div id="dd_content_${q.questions_id}" class="dropdown-content w-100">
                            <input type="text" placeholder="Cari..." id="dd_search_${q.questions_id}" class="form-control farm-search-input" onkeyup="filterOptions(${q.questions_id})">
                            ${optionsHTML}
                        </div>
                    </div>`;
            } else {
                const optionsHTML = q.options.map(opt => `<option value="${opt.option_text}">${opt.option_text}</option>`).join('');
                formGroup.innerHTML += `<select ${commonAttrs}><option value="">-- Pilih --</option>${optionsHTML}</select>`;
            }
        } else if (q.type === 'radio' && q.options) {
            const optionsHTML = q.options.map(opt => `
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="q${q.questions_id}" id="radio_${q.questions_id}_${opt.options_id || opt.option_text}" value="${opt.option_text}" ${isRequired ? 'required' : ''}>
                    <label class="form-check-label" for="radio_${q.questions_id}_${opt.options_id || opt.option_text}">${opt.option_text}</label>
                </div>`).join('');
            formGroup.innerHTML += optionsHTML;
        } else if (q.type === 'date') {
            formGroup.innerHTML += `<input type="date" ${commonAttrs}>`;
        } else if (q.type === 'textarea') {
            formGroup.innerHTML += `<textarea ${commonAttrs} rows="3" placeholder="Masukkan catatan..." style="resize: none !important; overflow: hidden; scrollbar-width: none; -ms-overflow-style: none;" oninput="autoResize(this)"></textarea>`;
        } else {
            let inputHTML;
            
            if (q.field_name === 'petelur_umur') {
                inputHTML = `<div class="integer-input"><input type="text" ${commonAttrs} placeholder="Akan terisi otomatis" readonly class="auto-calculated"></div>`;
                inputHTML += `<div class="auto-calc-info">* Umur dihitung otomatis berdasarkan tanggal chick-in</div>`;
            } else if (shouldBeCurrencyType(q.field_name)) {
                inputHTML = `<div class="currency-input"><span class="currency-prefix">Rp</span><input type="text" ${commonAttrs} placeholder="0" oninput="formatWithComma(this)" onkeypress="return isIntegerKey(event)"></div>`;
            } else if (shouldBeIntegerType(q.field_name)) {
                inputHTML = `<div class="integer-input"><input type="text" ${commonAttrs} placeholder="Masukkan angka bulat" oninput="formatWithComma(this)" onkeypress="return isIntegerKey(event)"></div>`;
            } else if (shouldHaveDecimalSupport(q.field_name)) {
                inputHTML = `<input type="text" inputmode="decimal" ${commonAttrs} placeholder="Masukkan angka" onkeypress="return isNumberKey(event)">`;
            } else if (shouldBeVarcharType(q.field_name)) {
                inputHTML = `<input type="text" ${commonAttrs} placeholder="Masukkan jawaban" onkeypress="return isAlphanumericKey(event)">`;
            } else {
                inputHTML = `<input type="text" ${commonAttrs} placeholder="Masukkan teks" onkeypress="return isLetterKey(event)">`;
            }
            formGroup.innerHTML += inputHTML;

            if (q.field_name === 'efektif_terisi_non_cp_petelur' || q.field_name === 'layer_selain_pakan_cp') {
                formGroup.innerHTML += `
                    <div class="d-flex align-items-center" style="margin-top: 5px;">
                        <div id="kapasitas_caption_div" class="auto-calc-info farm-capacity-info me-2">Kapasitas farm: -</div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="refreshKapasitasBtn" onclick="refreshFarmData(this)" title="Refresh data farm">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>`;
            }
        }
        return formGroup;
    }

    // **PERBAIKAN: Logika untuk tombol refresh**
    function enableDependentElements(enable) {
        document.querySelectorAll('.question-group').forEach(g => {
            if (g.dataset.field !== 'tipe_ternak') {
                g.querySelectorAll('input, select, button, textarea').forEach(el => {
                    if (el.dataset.field === 'petelur_umur') return; // Jangan disable field umur
                    if (el.id === 'refreshKapasitasBtn') return; // Jangan disable tombol refresh
                    
                    el.disabled = !enable;
                    if (el.tagName === 'BUTTON') enable ? el.classList.remove('disabled') : el.classList.add('disabled');
                });
            }
        });

        // Tampilkan/sembunyikan tombol refresh
        const refreshBtn = document.getElementById('refreshKapasitasBtn');
        if (refreshBtn) {
            refreshBtn.style.display = enable ? 'inline-block' : 'none';
        }
    }

    // --- SISTEM VALIDASI BARU (TIDAK BERUBAH) ---
    function validateSingleField(container) {
        const label = container.querySelector('label');
        if (!label || !label.querySelector('.text-danger')) {
            return true;
        }

        const questionText = label.innerText.replace('*', '').trim();
        let isValid = true;
        let errorMessage = '';
        let errorElement = null;

        const inputs = container.querySelectorAll('input, select, textarea');
        const customDropdown = container.querySelector('.custom-dropdown input[type="hidden"]');

        if (customDropdown) {
            isValid = customDropdown.value.trim() !== '';
            errorMessage = `${questionText} wajib dipilih.`;
            errorElement = container.querySelector('.dropdown-toggle');
        } else if (inputs.length > 0) {
            const input = inputs[0];
            if (input.type === 'radio') {
                const groupName = input.name;
                const checked = container.querySelector(`input[name="${groupName}"]:checked`);
                isValid = checked !== null;
                errorMessage = `${questionText} wajib dipilih.`;
                errorElement = container.querySelector('.form-check');
            } else if (input.tagName.toLowerCase() === 'select') {
                isValid = input.value.trim() !== '';
                errorMessage = `${questionText} wajib dipilih.`;
                errorElement = input;
            } else {
                isValid = input.value.trim() !== '';
                errorMessage = `${questionText} wajib diisi.`;
                errorElement = input;
            }
        }

        const existingError = container.querySelector('.field-error-msg');
        if (!isValid) {
            if (!existingError) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'field-error-msg text-danger';
                errorDiv.textContent = errorMessage;
                container.appendChild(errorDiv);
            }
            if (errorElement) errorElement.classList.add('is-invalid');
        } else {
            if (existingError) {
                existingError.remove();
            }
            if (errorElement) errorElement.classList.remove('is-invalid');
        }
        return isValid;
    }

    function validateAllRequiredFields() {
        let allFieldsValid = true;
        const errorFields = [];

        document.querySelectorAll('.question-group').forEach(container => {
            if (container.offsetParent !== null) { // Cek jika elemen terlihat
                const isValid = validateSingleField(container);
                if (!isValid) {
                    allFieldsValid = false;
                    errorFields.push(container);
                }
            }
        });

        if (!allFieldsValid && errorFields.length > 0) {
            errorFields[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        
        return allFieldsValid;
    }
    
    function addRealTimeValidation() {
        document.querySelectorAll('.question-group').forEach(container => {
            const inputs = container.querySelectorAll('input:not([type="hidden"]), select, textarea');
            
            inputs.forEach(input => {
                const eventType = (input.type === 'radio' || input.tagName.toLowerCase() === 'select') ? 'change' : 'input';
                input.removeEventListener(eventType, handleInputValidation); // Hapus listener lama
                input.addEventListener(eventType, handleInputValidation); // Tambah listener baru
            });
        });
    }

    function handleInputValidation(event) {
        const container = event.target.closest('.question-group');
        if (container) {
             if (event.target.value.trim() !== '' || (event.target.type === 'radio' && event.target.checked)) {
                const errorMsg = container.querySelector('.field-error-msg');
                if (errorMsg) {
                    errorMsg.remove();
                }
                event.target.classList.remove('is-invalid');
                if(event.target.type === 'radio') {
                    // Hapus error dari semua radio button di grup
                    container.querySelectorAll('.form-check').forEach(el => el.classList.remove('is-invalid'));
                }
            }
        }
    }

    // --- DOM CONTENT LOADED (DIPERBARUI) ---
    document.addEventListener('DOMContentLoaded', function() {
        enableDependentElements(false);
        document.addEventListener('click', e => { if (!e.target.closest('.custom-dropdown')) document.querySelectorAll('.dropdown-content.show').forEach(d => d.classList.remove('show')); });
        
        // Tambahkan hidden fields
        document.getElementById('petelurForm').innerHTML += `
            <input type="hidden" id="latitude" name="latitude">
            <input type="hidden" id="longitude" name="longitude">
            <input type="hidden" id="location_address" name="location_address">
        `;

        // Hapus pemanggilan 'addRealTimeValidation' dan 'startPetelurAgeCalculation' dari sini
        // karena mereka akan dipanggil oleh updateQuestionsDisplay()
    });

    // --- SUBMIT HANDLER (DIPERBARUI TOTAL) ---
    document.getElementById('petelurForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        calculatePetelurAge(); // Pastikan umur terbaru terhitung
        
        // 1. Jalankan validasi wajib isi
        if (!validateAllRequiredFields()) {
            return;
        }

        // 2. **VALIDASI KAPASITAS BARU (TERMASUK LOGIKA PUYUH, BEBEK, ARAP)**
        if (currentSelectedKapasitas > 0) {
            const tipeTernak = document.getElementById('selected_tipe_ternak').value;
            let totalTerisi = 0;
            let errorInput = null;

            if (tipeTernak === 'Layer') {
                // Logika khusus Layer: jumlahkan dua field
                const pakanCPInput = document.querySelector('input[data-field="layer_pakai_pakan_cp"]');
                const pakanLainInput = document.querySelector('input[data-field="layer_selain_pakan_cp"]');
                
                const valCP = pakanCPInput ? parseInt(pakanCPInput.value.replace(/,/g, ''), 10) : 0;
                const valLain = pakanLainInput ? parseInt(pakanLainInput.value.replace(/,/g, ''), 10) : 0;
                
                totalTerisi = (isNaN(valCP) ? 0 : valCP) + (isNaN(valLain) ? 0 : valLain);
                errorInput = pakanLainInput || pakanCPInput; // Target error ke field terakhir

            } else {
                // **Logika petelur lain (Puyuh, Bebek, Arap): Jumlahkan efektif_terisi_cp_petelur & efektif_terisi_non_cp_petelur**
                const efektifCPInput = document.querySelector('input[data-field="efektif_terisi_cp_petelur"]');
                const efektifNonCPInput = document.querySelector('input[data-field="efektif_terisi_non_cp_petelur"]');
                
                const valCP = efektifCPInput ? parseInt(efektifCPInput.value.replace(/,/g, ''), 10) : 0;
                const valNonCP = efektifNonCPInput ? parseInt(efektifNonCPInput.value.replace(/,/g, ''), 10) : 0;
                
                totalTerisi = (isNaN(valCP) ? 0 : valCP) + (isNaN(valNonCP) ? 0 : valNonCP);
                errorInput = efektifNonCPInput || efektifCPInput; // Target error ke field terakhir
            }

            // Cek jika melebihi kapasitas
            // ... (lanjutan kode validasi kapasitas) ...

            // Cek jika melebihi kapasitas
            if (!isNaN(totalTerisi) && totalTerisi > currentSelectedKapasitas) {
                const totalFormatted = totalTerisi.toLocaleString('en-US');
                const kapasitasFormatted = currentSelectedKapasitas.toLocaleString('en-US');
                const adminFarmUrl = "<?=site_url('Admin_Controller/Farm')?>";

                Swal.fire({
                    icon: 'error',
                    title: 'Kapasitas Terlampaui',
                    html: `Jumlah total "Efektif Terisi" (<b>${totalFormatted}</b>) melebihi kapasitas farm (<b>${kapasitasFormatted}</b>).<br><br>Apakah Anda ingin memperbarui data kapasitas farm?`,
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Perbarui Kapasitas',
                    cancelButtonText: 'Tidak, Perbaiki Input',
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.open(adminFarmUrl, '_blank');
                    }
                });

                if (errorInput) {
                    errorInput.classList.add('is-invalid');
                    errorInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                
                return; // Hentikan submit
            }
        }
        // --- AKHIR VALIDASI KAPASITAS ---

        
        // 3. Lanjutkan ke proses submit jika lolos
        const submitBtn = document.getElementById('submit-btn');
        const loading = document.getElementById('loading');
        
        submitBtn.disabled = true;
        loading.style.display = 'block';
        
        try {
            const position = await getCurrentLocation();
            const { latitude, longitude } = position.coords;
            
            try {
                const response = await fetch(
                    `https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}&zoom=18&addressdetails=1&accept-language=id`, {
                        method: 'GET',
                        headers: { 'User-Agent': 'Mozilla/5.0', 'Accept': 'application/json', 'Accept-Language': 'id' },
                        referrerPolicy: 'no-referrer'
                    }
                );

                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                
                const data = await response.json();
                if (!data.display_name) throw new Error('No address found');

                document.getElementById('latitude').value = latitude;
                document.getElementById('longitude').value = longitude;
                document.getElementById('location_address').value = data.display_name;
                
                // **PERBAIKAN: Gunakan 'e.target' (form) bukan 'this'**
                e.target.querySelectorAll('input[oninput*="formatWithComma"]').forEach(i => { 
                    i.value = i.value.replace(/,/g, ''); 
                });
                
                // **PERBAIKAN: Gunakan 'e.target.submit()'**
                e.target.submit();
                
            } catch (error) {
                console.error('Address fetch error:', error);
                throw new Error(`Failed to get address: ${error.message}`);
            }
        } catch (error) {
            console.error('Error:', error);
            alert(error.message);
            submitBtn.disabled = false;
            loading.style.display = 'none';
        }
    });

    // --- FUNGSI GEOLOCATION (TIDAK BERUBAH) ---
    function getCurrentLocation() {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                reject(new Error('Browser tidak mendukung geolocation'));
                return;
            }
            
            navigator.geolocation.getCurrentPosition(
                (position) => { resolve(position); },
                (error) => {
                    let message = 'Location error: ';
                    switch(error.code) {
                        case error.PERMISSION_DENIED: message += 'Please enable location access'; break;
                        case error.POSITION_UNAVAILABLE: message += 'Location information unavailable'; break;
                        case error.TIMEOUT: message += 'Location request timed out'; break;
                        default: message += 'Unknown error occurred';
                    }
                    reject(new Error(message));
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        });
    }
    </script>
</body>
</html>
