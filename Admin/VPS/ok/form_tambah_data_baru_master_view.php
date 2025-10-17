<!DOCTYPE html>
<html>
<head>
    <title><?= isset($page_title) ? $page_title : 'Form Tambah Data Baru' ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-container { margin-left: 20px; }
        .page-title { margin-left: 10px; }
        .question-group { margin-bottom: 15px; }
        .dependent-field { display: none; }
        .custom-dropdown { position: relative; max-width: 400px; }
        .dropdown-toggle { 
            background: #fff; 
            color: #333; 
            border: 1px solid #dee2e6; 
            cursor: pointer; 
            text-align: left; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
        }
        .dropdown-toggle:hover, .dropdown-toggle:focus { 
            background: #f8f9fa; 
            border-color: #0d6efd; 
        }
        .dropdown-toggle::after { 
            content: "▼"; 
            font-size: 12px; 
        }
        .dropdown-content { 
            display: none; 
            position: absolute; 
            background: #fff; 
            min-width: 100%; 
            max-height: 200px; 
            overflow-y: auto; 
            border: 1px solid #dee2e6; 
            border-radius: 0 0 .375rem .375rem; 
            border-top: none; 
            z-index: 1000; 
            box-shadow: 0 .125rem .25rem rgba(0,0,0,.075);
        }
        .dropdown-content .dropdown-option { 
            color: #333; 
            padding: 10px 12px; 
            text-decoration: none; 
            display: block; 
            cursor: pointer; 
            border-bottom: 1px solid #eee;
        }
        .dropdown-content .dropdown-option:hover { 
            background: #f8f9fa; 
        }
        .show { display: block; }
        .selected-item { background: #fff; border-color: #dee2e6; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <h2 class="page-title mb-4"><?= isset($page_title) ? $page_title : 'Form Tambah Data Baru' ?></h2>

        <form method="post" action="" class="form-container" id="mainForm">
            <!-- Hidden fields untuk location (hanya untuk Farm dan Sub Agen) -->
            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">
            <input type="hidden" name="location_address" id="location_address">

            <?php if (!empty($questions_kategori)): ?>
                <!-- Tambahkan di dalam loop questions, ganti bagian untuk render field -->
                <?php foreach ($questions_kategori as $q): ?>
                    <div class="question-group <?= (in_array($q['field_name'], ['agen_dari', 'sub_agen_dari', 'kemitraan_dari'])) ? 'dependent-field' : '' ?>" 
                        id="field-<?= $q['field_name'] ?>"
                        <?= ($q['field_name'] == 'jenis_peternak') ? 'data-jenis-peternak="true"' : '' ?>
                        <?php 
                        // Logika untuk menyembunyikan field tertentu di Pakan
                        if ($kategori_selected == 'Pakan'):
                            if ($q['field_name'] == 'pilihan_pakan'): ?>
                                style="display: none;"
                            <?php elseif ($q['field_name'] == 'nama_pakan'): ?>
                                data-pakan-nama="true"
                            <?php elseif ($q['field_name'] == 'layer_pilihan_pakan_cp'): ?>
                                data-pakan-cp="true" style="display: none;"
                            <?php elseif ($q['field_name'] == 'layer_pilihan_pakan_lain'): ?>
                                data-pakan-noncp="true" style="display: none;"
                            <?php endif;
                        endif;
                        ?>
                    >
                        <label class="form-label fw-bold mb-1">
                            <?= $q['question_text'] ?>
                            <?php if (!empty($q['required'])): ?>
                                <span class="text-danger">*</span>
                            <?php endif; ?>
                        </label>
                        
                        <!-- Field rendering tetap sama seperti sebelumnya -->
                        <?php if ($q['type'] == 'number' || $q['field_name'] == 'kapasitas_peternak' || $q['field_name'] == 'jumlah_kandang_peternak' || $q['field_name'] == 'kapasitas_farm'): ?>
                            <input type="text"
                                inputmode="numeric" 
                                class="form-control mt-1 numeric-input"
                                style="max-width: 400px"
                                name="q<?= $q['questions_id'] ?>" 
                                placeholder="Masukkan angka"
                                <?= !empty($q['required']) ? 'required' : '' ?>>

                        <?php elseif ($q['type'] == 'text'): ?>
                            <input type="text" 
                                name="q<?= $q['questions_id'] ?>" 
                                class="form-control mt-1"
                                style="max-width: 400px"
                                placeholder="Masukkan jawaban"
                                <?= !empty($q['required']) ? 'required' : '' ?>>
                                    
                        <?php elseif ($q['type'] == 'date'): ?>
                            <input type="date" 
                                name="q<?= $q['questions_id'] ?>" 
                                class="form-control mt-1"
                                style="max-width: 400px"
                                <?= !empty($q['required']) ? 'required' : '' ?>>

                        <?php elseif ($q['type'] == 'radio' && !empty($q['options'])): ?>
                            <div class="mt-1">
                                <?php foreach ($q['options'] as $opt): ?>
                                    <div class="form-check my-1">
                                        <input class="form-check-input" 
                                            type="radio" 
                                            name="q<?= $q['questions_id'] ?>" 
                                            value="<?= htmlspecialchars($opt['option_text'], ENT_QUOTES) ?>"
                                            id="r_<?= $q['questions_id'] ?>_<?= $opt['options_id'] ?? rand() ?>"
                                            <?= !empty($q['required']) ? 'required' : '' ?>
                                            <?php if ($kategori_selected == 'Pakan' && $q['field_name'] == 'pilihan_pakan'): ?>
                                                onchange="toggleLayerPakanFields(this.value)"
                                            <?php endif; ?>>
                                        <label class="form-check-label" 
                                            for="r_<?= $q['questions_id'] ?>_<?= $opt['options_id'] ?? rand() ?>">
                                            <?= htmlspecialchars($opt['option_text']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        <?php elseif ($q['type'] == 'select'): ?>
                            <?php if ($q['field_name'] == 'jenis_peternak'): ?>
                                <select name="q<?= $q['questions_id'] ?>" 
                                        class="form-select mt-1"
                                        style="max-width: 400px"
                                        <?= !empty($q['required']) ? 'required' : '' ?>
                                        onchange="toggleDependentFields(this.value)">
                                    <option value="">-- Pilih Jawaban --</option>
                                    <?php if (!empty($q['options'])): ?>
                                        <?php foreach ($q['options'] as $opt): ?>
                                            <option value="<?= htmlspecialchars($opt['option_text'], ENT_QUOTES) ?>">
                                                <?= htmlspecialchars($opt['option_text']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>Tidak ada opsi tersedia</option>
                                    <?php endif; ?>
                                </select>
                            <?php elseif ($kategori_selected == 'Pakan' && $q['field_name'] == 'tipe_ternak'): ?>
                                <select name="q<?= $q['questions_id'] ?>" 
                                        class="form-select mt-1"
                                        style="max-width: 400px"
                                        <?= !empty($q['required']) ? 'required' : '' ?>
                                        onchange="togglePakanFieldsByTipeTermak(this.value)">
                                    <option value="">-- Pilih Jawaban --</option>
                                    <?php if (!empty($q['options'])): ?>
                                        <?php foreach ($q['options'] as $opt): ?>
                                            <option value="<?= htmlspecialchars($opt['option_text'], ENT_QUOTES) ?>">
                                                <?= htmlspecialchars($opt['option_text']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>Tidak ada opsi tersedia</option>
                                    <?php endif; ?>
                                </select>
                            <?php else: ?>
                                <select name="q<?= $q['questions_id'] ?>" 
                                        class="form-select mt-1"
                                        style="max-width: 400px"
                                        <?= !empty($q['required']) ? 'required' : '' ?>>
                                    <option value="">-- Pilih Jawaban --</option>
                                    <?php if (!empty($q['options'])): ?>
                                        <?php foreach ($q['options'] as $opt): ?>
                                            <option value="<?= htmlspecialchars($opt['option_text'], ENT_QUOTES) ?>">
                                                <?= htmlspecialchars($opt['option_text']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>Tidak ada opsi tersedia</option>
                                    <?php endif; ?>
                                </select>
                            <?php endif; ?>

                        <?php elseif ($q['type'] == 'checkbox' && !empty($q['options'])): ?>
                            <div class="mt-1">
                                <?php foreach ($q['options'] as $opt): ?>
                                    <div class="form-check my-1">
                                        <input class="form-check-input" 
                                            type="checkbox" 
                                            name="q<?= $q['questions_id'] ?>[]" 
                                            value="<?= htmlspecialchars($opt['option_text'], ENT_QUOTES) ?>"
                                            id="c_<?= $q['questions_id'] ?>_<?= $opt['options_id'] ?? rand() ?>">
                                        <label class="form-check-label" 
                                            for="c_<?= $q['questions_id'] ?>_<?= $opt['options_id'] ?? rand() ?>">
                                            <?= htmlspecialchars($opt['option_text']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        <?php elseif ($q['type'] == 'textarea'): ?>
                            <textarea name="q<?= $q['questions_id'] ?>"
                                    class="form-control mt-1"
                                    rows="4" 
                                    style="max-width: 400px"
                                    placeholder="Masukkan jawaban"
                                    <?= !empty($q['required']) ? 'required' : '' ?>></textarea>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                            
                <button type="submit" name="submit_form" value="1" class="btn btn-primary px-4 py-2 mt-4">Submit</button>
            <?php else: ?>
                <div class="alert alert-info">
                    <p class="mb-0 fst-italic">Tidak ada pertanyaan yang tersedia untuk kategori ini.</p>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tambahkan fungsi baru untuk handle logika Pakan Layer
        function togglePakanFieldsByTipeTermak(selectedValue) {
            const namaPakanField = document.querySelector('[data-pakan-nama="true"]');
            const pilihanPakanField = document.getElementById('field-pilihan_pakan');
            const cpField = document.querySelector('[data-pakan-cp="true"]');
            const nonCpField = document.querySelector('[data-pakan-noncp="true"]');
            
            if (selectedValue === 'Layer') {
                // Jika Layer, sembunyikan nama_pakan dan tampilkan pilihan_pakan
                if (namaPakanField) {
                    namaPakanField.style.display = 'none';
                    const inputs = namaPakanField.querySelectorAll('input, select, textarea');
                    inputs.forEach(input => {
                        input.removeAttribute('required');
                        if (input.type === 'radio' || input.type === 'checkbox') {
                            input.checked = false;
                        } else {
                            input.value = '';
                        }
                    });
                }
                
                if (pilihanPakanField) {
                    pilihanPakanField.style.display = 'block';
                    const qData = window.questionsData.find(q => q.field_name === 'pilihan_pakan');
                    if (qData && qData.required == '1') {
                        const inputs = pilihanPakanField.querySelectorAll('input, select, textarea');
                        inputs.forEach(input => {
                            input.setAttribute('required', 'required');
                        });
                    }
                }
                
                // Sembunyikan field CP dan Non CP dulu (akan ditampilkan setelah pilih pilihan_pakan)
                if (cpField) {
                    cpField.style.display = 'none';
                    resetFieldInputs(cpField);
                }
                if (nonCpField) {
                    nonCpField.style.display = 'none';
                    resetFieldInputs(nonCpField);
                }
                
            } else {
                // Jika bukan Layer, tampilkan nama_pakan dan sembunyikan yang lain
                if (namaPakanField) {
                    namaPakanField.style.display = 'block';
                    const qData = window.questionsData.find(q => q.field_name === 'nama_pakan');
                    if (qData && qData.required == '1') {
                        const inputs = namaPakanField.querySelectorAll('input, select, textarea');
                        inputs.forEach(input => {
                            input.setAttribute('required', 'required');
                        });
                    }
                }
                
                if (pilihanPakanField) {
                    pilihanPakanField.style.display = 'none';
                    resetFieldInputs(pilihanPakanField);
                }
                
                if (cpField) {
                    cpField.style.display = 'none';
                    resetFieldInputs(cpField);
                }
                
                if (nonCpField) {
                    nonCpField.style.display = 'none';
                    resetFieldInputs(nonCpField);
                }
            }
        }

        // Fungsi baru untuk handle pilihan CP atau Non CP
        function toggleLayerPakanFields(selectedValue) {
            const cpField = document.querySelector('[data-pakan-cp="true"]');
            const nonCpField = document.querySelector('[data-pakan-noncp="true"]');
            
            if (selectedValue === 'CP') {
                // Tampilkan field CP, sembunyikan Non CP
                if (cpField) {
                    cpField.style.display = 'block';
                    const qData = window.questionsData.find(q => q.field_name === 'layer_pilihan_pakan_cp');
                    if (qData && qData.required == '1') {
                        const inputs = cpField.querySelectorAll('input, select, textarea');
                        inputs.forEach(input => {
                            input.setAttribute('required', 'required');
                        });
                    }
                }
                
                if (nonCpField) {
                    nonCpField.style.display = 'none';
                    resetFieldInputs(nonCpField);
                }
                
            } else if (selectedValue === 'Non CP') {
                // Tampilkan field Non CP, sembunyikan CP
                if (nonCpField) {
                    nonCpField.style.display = 'block';
                    const qData = window.questionsData.find(q => q.field_name === 'layer_pilihan_pakan_lain');
                    if (qData && qData.required == '1') {
                        const inputs = nonCpField.querySelectorAll('input, select, textarea');
                        inputs.forEach(input => {
                            input.setAttribute('required', 'required');
                        });
                    }
                }
                
                if (cpField) {
                    cpField.style.display = 'none';
                    resetFieldInputs(cpField);
                }
            }
        }

        // Helper function untuk reset inputs
        function resetFieldInputs(fieldElement) {
            if (!fieldElement) return;
            
            const inputs = fieldElement.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.removeAttribute('required');
                if (input.type === 'radio' || input.type === 'checkbox') {
                    input.checked = false;
                } else {
                    input.value = '';
                }
            });
        }

        // Fungsi existing togglePilihanPakanField - TETAP DIPERTAHANKAN untuk backward compatibility
        function togglePilihanPakanField(selectedValue) {
            const pilihanPakanField = document.getElementById('field-pilihan_pakan');
            if (!pilihanPakanField) return;

            const inputs = pilihanPakanField.querySelectorAll('input, select, textarea');
            const qData = window.questionsData.find(q => q.field_name === 'pilihan_pakan');

            if (selectedValue === 'Layer') {
                pilihanPakanField.style.display = 'block';
                
                if (qData && qData.required == '1') {
                    inputs.forEach(input => {
                        input.setAttribute('required', 'required');
                    });
                }
            } else {
                pilihanPakanField.style.display = 'none';
                
                inputs.forEach(input => {
                    input.removeAttribute('required');
                    if (input.type === 'radio' || input.type === 'checkbox') {
                        input.checked = false;
                    } else {
                        input.value = '';
                    }
                });
            }
        }

        // Update DOMContentLoaded untuk inisialisasi field Pakan
        document.addEventListener('DOMContentLoaded', function() {
            window.questionsData = <?= json_encode($questions_kategori) ?>;
            
            // Logic for dependent fields (Peternak) - TETAP DIPERTAHANKAN
            const jenisPeternakGroup = document.querySelector('[data-jenis-peternak="true"]');
            if (jenisPeternakGroup) {
                const jenisPeternakSelect = jenisPeternakGroup.querySelector('select');
                if (jenisPeternakSelect) {
                    toggleDependentFields(jenisPeternakSelect.value);
                }
            }

            // Inisialisasi untuk kategori Pakan - UPDATE LOGIC
            const kategori = "<?= $kategori_selected ?>";
            if (kategori === 'Pakan') {
                const tipeTernakSelect = document.querySelector('select[onchange*="togglePakanFieldsByTipeTermak"]');
                if (tipeTernakSelect) {
                    togglePakanFieldsByTipeTermak(tipeTernakSelect.value);
                }
            }

            // Number formatting - TETAP DIPERTAHANKAN
            function formatNumber(e) {
                let input = e.target;
                let value = input.value.replace(/,/g, '');
                
                if (value.trim() === '') {
                    input.value = '';
                    return;
                }
                
                let cursorPosition = input.selectionStart;
                const lengthBefore = input.value.length;
                
                input.value = new Intl.NumberFormat('en-US').format(value);
                
                const lengthAfter = input.value.length;
                cursorPosition += (lengthAfter - lengthBefore);
                input.setSelectionRange(cursorPosition, cursorPosition);
            }

            document.querySelectorAll('.numeric-input').forEach(input => {
                input.addEventListener('input', formatNumber);
                if (input.value) {
                    formatNumber({ target: input });
                }
            });

            // Location handling - TETAP DIPERTAHANKAN
            const needsLocation = (kategori === 'Farm' || kategori === 'Sub Agen');
            
            const mainForm = document.getElementById('mainForm');
            let locationCaptured = false;
            
            mainForm.addEventListener('submit', async function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                
                document.querySelectorAll('.numeric-input').forEach(input => {
                    input.value = input.value.replace(/,/g, '');
                });
                
                if (!needsLocation) {
                    console.log('No location needed, submitting...');
                    return;
                }
                
                if (locationCaptured) {
                    console.log('Location already captured, submitting...');
                    return;
                }
                
                e.preventDefault();
                console.log('Capturing location before submit...');
                
                submitBtn.disabled = true;
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'Mengambil Lokasi...';
                
                try {
                    const position = await getCurrentLocation();
                    const { latitude, longitude } = position.coords;
                    
                    const formattedLat = parseFloat(latitude).toFixed(7);
                    const formattedLon = parseFloat(longitude).toFixed(7);

                    console.log('Got coordinates:', formattedLat, formattedLon);
                    
                    document.getElementById('latitude').value = formattedLat;
                    document.getElementById('longitude').value = formattedLon;
                    
                    submitBtn.textContent = 'Mengambil Alamat...';
                    const address = await getAddressFromCoordinates(formattedLat, formattedLon);
                    document.getElementById('location_address').value = address;
                    
                    console.log('Location captured successfully');
                    
                    locationCaptured = true;
                    
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Mengirim Data...';
                    
                    console.log('Re-triggering form submit...');
                    submitBtn.click();
                    
                } catch (error) {
                    console.error('Location error:', error);
                    alert(error.message);
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    locationCaptured = false;
                }
            });
        });

        // Fungsi khusus untuk toggle pilihan pakan (CP/Non CP) ketika pilih Layer
        function togglePilihanPakanField(selectedValue) {
            const pilihanPakanField = document.getElementById('field-pilihan_pakan');
            if (!pilihanPakanField) return;

            const inputs = pilihanPakanField.querySelectorAll('input, select, textarea');
            const qData = window.questionsData.find(q => q.field_name === 'pilihan_pakan');

            if (selectedValue === 'Layer') {
                // Tampilkan field pilihan pakan
                pilihanPakanField.style.display = 'block';
                
                // Set required jika memang required di database
                if (qData && qData.required == '1') {
                    inputs.forEach(input => {
                        input.setAttribute('required', 'required');
                    });
                }
            } else {
                // Sembunyikan field pilihan pakan
                pilihanPakanField.style.display = 'none';
                
                // Hapus required dan reset value
                inputs.forEach(input => {
                    input.removeAttribute('required');
                    if (input.type === 'radio' || input.type === 'checkbox') {
                        input.checked = false;
                    } else {
                        input.value = '';
                    }
                });
            }
        }

        // Fungsi existing untuk toggle dependent fields (Peternak)
        function toggleDependentFields(selectedValue) {
            const dependentFields = document.querySelectorAll('.dependent-field');
            dependentFields.forEach(field => {
                field.style.display = 'none';
                const inputs = field.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    input.removeAttribute('required');
                });
            });

            const fieldsToShow = {
                'Agen': 'field-agen_dari',
                'Sub Agen': 'field-sub_agen_dari',
                'Kemitraan': 'field-kemitraan_dari'
            };

            const fieldToShowId = fieldsToShow[selectedValue];
            if (fieldToShowId) {
                const fieldElement = document.getElementById(fieldToShowId);
                if (fieldElement) {
                    fieldElement.style.display = 'block';
                    const qData = window.questionsData.find(q => 'field-' + q.field_name === fieldToShowId);
                    if (qData && qData.required) {
                        fieldElement.querySelectorAll('input, select, textarea').forEach(input => {
                            input.setAttribute('required', 'required');
                        });
                    }
                }
            }
        }

        function getCurrentLocation() {
            return new Promise((resolve, reject) => {
                if (!navigator.geolocation) {
                    reject(new Error('Geolocation tidak didukung oleh browser Anda'));
                    return;
                }
                
                navigator.geolocation.getCurrentPosition(
                    resolve,
                    (error) => {
                        let message = 'Gagal mendapatkan lokasi: ';
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                message += 'Izin akses lokasi ditolak. Silakan aktifkan izin lokasi di pengaturan browser.';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                message += 'Informasi lokasi tidak tersedia.';
                                break;
                            case error.TIMEOUT:
                                message += 'Waktu permintaan lokasi habis.';
                                break;
                            default:
                                message += 'Terjadi kesalahan tidak diketahui.';
                        }
                        reject(new Error(message));
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 15000,
                        maximumAge: 0
                    }
                );
            });
        }

        async function getAddressFromCoordinates(lat, lon) {
            try {
                const response = await fetch(
                    `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=18&addressdetails=1`,
                    {
                        headers: {
                            'User-Agent': 'CP-APPS/1.0'
                        }
                    }
                );
                
                if (!response.ok) {
                    throw new Error('Gagal mengambil alamat');
                }
                
                const data = await response.json();
                return data.display_name || 'Alamat tidak ditemukan';
            } catch (error) {
                console.warn('Error getting address:', error);
                return 'Alamat tidak dapat diambil';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Store questions data globally
            window.questionsData = <?= json_encode($questions_kategori) ?>;
            
            // Logic for dependent fields (Peternak)
            const jenisPeternakGroup = document.querySelector('[data-jenis-peternak="true"]');
            if (jenisPeternakGroup) {
                const jenisPeternakSelect = jenisPeternakGroup.querySelector('select');
                if (jenisPeternakSelect) {
                    toggleDependentFields(jenisPeternakSelect.value);
                }
            }

            // Inisialisasi field pilihan pakan untuk kategori Pakan
            const kategori = "<?= $kategori_selected ?>";
            if (kategori === 'Pakan') {
                const tipeTernakSelect = document.querySelector('#field-tipe_ternak select');
                if (tipeTernakSelect) {
                    togglePilihanPakanField(tipeTernakSelect.value);
                }
            }

            // Number formatting function
            function formatNumber(e) {
                let input = e.target;
                let value = input.value.replace(/,/g, '');
                
                if (value.trim() === '') {
                    input.value = '';
                    return;
                }
                
                let cursorPosition = input.selectionStart;
                const lengthBefore = input.value.length;
                
                input.value = new Intl.NumberFormat('en-US').format(value);
                
                const lengthAfter = input.value.length;
                cursorPosition += (lengthAfter - lengthBefore);
                input.setSelectionRange(cursorPosition, cursorPosition);
            }

            // Apply number formatting
            document.querySelectorAll('.numeric-input').forEach(input => {
                input.addEventListener('input', formatNumber);
                if (input.value) {
                    formatNumber({ target: input });
                }
            });

            const needsLocation = (kategori === 'Farm' || kategori === 'Sub Agen');
            
            // Form submit handler
            const mainForm = document.getElementById('mainForm');
            let locationCaptured = false;
            
            mainForm.addEventListener('submit', async function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                
                // Clean numeric inputs first
                document.querySelectorAll('.numeric-input').forEach(input => {
                    input.value = input.value.replace(/,/g, '');
                });
                
                // If location is NOT needed, let form submit normally
                if (!needsLocation) {
                    console.log('No location needed, submitting...');
                    return;
                }
                
                // If location IS needed AND already captured, let it submit
                if (locationCaptured) {
                    console.log('Location already captured, submitting...');
                    return;
                }
                
                // Need to capture location first, prevent submit
                e.preventDefault();
                console.log('Capturing location before submit...');
                
                submitBtn.disabled = true;
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'Mengambil Lokasi...';
                
                try {
                    // Get current position
                    const position = await getCurrentLocation();
                    const { latitude, longitude } = position.coords;
                    
                    const formattedLat = parseFloat(latitude).toFixed(7);
                    const formattedLon = parseFloat(longitude).toFixed(7);

                    console.log('Got coordinates:', formattedLat, formattedLon);
                    
                    // Set coordinates
                    document.getElementById('latitude').value = formattedLat;
                    document.getElementById('longitude').value = formattedLon;
                    
                    // Get address
                    submitBtn.textContent = 'Mengambil Alamat...';
                    const address = await getAddressFromCoordinates(formattedLat, formattedLon);
                    document.getElementById('location_address').value = address;
                    
                    console.log('Location captured successfully');
                    console.log('Latitude:', document.getElementById('latitude').value);
                    console.log('longitude:', document.getElementById('longitude').value);
                    console.log('Address:', document.getElementById('location_address').value);
                    
                    // Mark location as captured
                    locationCaptured = true;
                    
                    // Re-enable button and trigger submit again
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Mengirim Data...';
                    
                    // Trigger form submit again (this time will pass through)
                    console.log('Re-triggering form submit...');
                    submitBtn.click();
                    
                } catch (error) {
                    console.error('Location error:', error);
                    alert(error.message);
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    locationCaptured = false;
                }
            });
        });
    </script>
</body>
</html>
