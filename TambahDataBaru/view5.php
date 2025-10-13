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
    </style>
</head>
<body>
    <div class="container-fluid">
        <h2 class="page-title mb-4"><?= isset($page_title) ? $page_title : 'Form Tambah Data Baru' ?></h2>

        <form method="post" action="" class="form-container" id="mainForm">
            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">
            <input type="hidden" name="location_address" id="location_address">

            <?php if (!empty($questions_kategori)): ?>
                <?php foreach ($questions_kategori as $q): ?>
                    <div class="question-group <?= (in_array($q['field_name'], ['agen_dari', 'sub_agen_dari', 'kemitraan_dari'])) ? 'dependent-field' : '' ?>" 
                         id="field-<?= $q['field_name'] ?>"
                         data-field-name="<?= $q['field_name'] ?>"
                         
                         <?php if ($q['field_name'] == 'pilihan_pakan'): ?>
                             style="display: none;"
                         <?php endif; ?>
                    >
                        <label class="form-label fw-bold mb-1">
                            <?= $q['question_text'] ?>
                            <?php if (!empty($q['required'])): ?>
                                <span class="text-danger">*</span>
                            <?php endif; ?>
                        </label>
                        
                        <?php if ($q['type'] == 'number' || in_array($q['field_name'], ['kapasitas_peternak', 'jumlah_kandang_peternak', 'kapasitas_farm'])): ?>
                            <input type="text" inputmode="numeric" class="form-control mt-1 numeric-input" style="max-width: 400px" name="q<?= $q['questions_id'] ?>" placeholder="Masukkan angka" <?= !empty($q['required']) ? 'required' : '' ?>>
                        
                        <?php elseif ($q['type'] == 'text'): ?>
                            <input type="text" name="q<?= $q['questions_id'] ?>" class="form-control mt-1" style="max-width: 400px" placeholder="Masukkan jawaban" <?= !empty($q['required']) ? 'required' : '' ?>>
                        
                        <?php elseif ($q['type'] == 'date'): ?>
                            <input type="date" name="q<?= $q['questions_id'] ?>" class="form-control mt-1" style="max-width: 400px" <?= !empty($q['required']) ? 'required' : '' ?>>

                        <?php elseif ($q['type'] == 'radio' && !empty($q['options'])): ?>
                            <div class="mt-1">
                                <?php foreach ($q['options'] as $opt): ?>
                                    <div class="form-check my-1">
                                        <input class="form-check-input" type="radio" name="q<?= $q['questions_id'] ?>" value="<?= htmlspecialchars($opt['option_text'], ENT_QUOTES) ?>" id="r_<?= $q['questions_id'] ?>_<?= $opt['options_id'] ?? rand() ?>" <?= !empty($q['required']) ? 'required' : '' ?>>
                                        <label class="form-check-label" for="r_<?= $q['questions_id'] ?>_<?= $opt['options_id'] ?? rand() ?>">
                                            <?= htmlspecialchars($opt['option_text']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        <?php elseif ($q['type'] == 'select'): ?>
                            <select name="q<?= $q['questions_id'] ?>" 
                                    class="form-select mt-1"
                                    style="max-width: 400px"
                                    <?= !empty($q['required']) ? 'required' : '' ?>
                                    
                                    <?php if ($q['field_name'] == 'jenis_peternak'): ?>
                                        onchange="toggleDependentFields(this.value)"
                                    <?php elseif ($kategori_selected == 'Pakan' && $q['field_name'] == 'tipe_ternak'): ?>
                                        onchange="togglePakanFields(this.value)"
                                    <?php endif; ?>
                            >
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

                        <?php elseif ($q['type'] == 'checkbox' && !empty($q['options'])): ?>
                            <div class="mt-1">
                                <?php foreach ($q['options'] as $opt): ?>
                                    <div class="form-check my-1">
                                        <input class="form-check-input" type="checkbox" name="q<?= $q['questions_id'] ?>[]" value="<?= htmlspecialchars($opt['option_text'], ENT_QUOTES) ?>" id="c_<?= $q['questions_id'] ?>_<?= $opt['options_id'] ?? rand() ?>">
                                        <label class="form-check-label" for="c_<?= $q['questions_id'] ?>_<?= $opt['options_id'] ?? rand() ?>">
                                            <?= htmlspecialchars($opt['option_text']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        <?php elseif ($q['type'] == 'textarea'): ?>
                            <textarea name="q<?= $q['questions_id'] ?>" class="form-control mt-1" rows="4" style="max-width: 400px" placeholder="Masukkan jawaban" <?= !empty($q['required']) ? 'required' : '' ?>></textarea>
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
        // Fungsi untuk pakan (CP/Non CP)
        function togglePakanFields(selectedValue) {
            const pilihanPakanField = document.getElementById('field-pilihan_pakan');
            if (!pilihanPakanField) return;

            const inputs = pilihanPakanField.querySelectorAll('input, select');
            const qData = window.questionsData.find(q => q.field_name === 'pilihan_pakan');

            if (selectedValue === 'Layer') {
                pilihanPakanField.style.display = 'block';
                if (qData && qData.required == '1') {
                    inputs.forEach(input => input.setAttribute('required', 'required'));
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

        // Fungsi untuk peternak (Agen, Sub Agen, dll)
        function toggleDependentFields(selectedValue) {
            const dependentFields = document.querySelectorAll('.dependent-field');
            dependentFields.forEach(field => {
                field.style.display = 'none';
                field.querySelectorAll('input, select, textarea').forEach(input => input.removeAttribute('required'));
            });

            const fieldsToShow = { 'Agen': 'field-agen_dari', 'Sub Agen': 'field-sub_agen_dari', 'Kemitraan': 'field-kemitraan_dari' };
            const fieldToShowId = fieldsToShow[selectedValue];
            if (fieldToShowId) {
                const fieldElement = document.getElementById(fieldToShowId);
                if (fieldElement) {
                    fieldElement.style.display = 'block';
                    const qData = window.questionsData.find(q => 'field-' + q.field_name === fieldToShowId);
                    if (qData && qData.required == '1') {
                        fieldElement.querySelectorAll('input, select, textarea').forEach(input => input.setAttribute('required', 'required'));
                    }
                }
            }
        }

        // ... (fungsi getCurrentLocation & getAddressFromCoordinates tidak perlu diubah) ...
        function getCurrentLocation() { return new Promise((resolve, reject)=>{if(!navigator.geolocation){reject(new Error('Geolocation tidak didukung oleh browser Anda'));return}navigator.geolocation.getCurrentPosition(resolve, (error)=>{let message='Gagal mendapatkan lokasi: ';switch(error.code){case error.PERMISSION_DENIED:message+='Izin akses lokasi ditolak. Silakan aktifkan izin lokasi di pengaturan browser.';break;case error.POSITION_UNAVAILABLE:message+='Informasi lokasi tidak tersedia.';break;case error.TIMEOUT:message+='Waktu permintaan lokasi habis.';break;default:message+='Terjadi kesalahan tidak diketahui.'}reject(new Error(message))},{enableHighAccuracy:true, timeout:15000, maximumAge:0})}); }
        async function getAddressFromCoordinates(lat, lon) { try { const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=18&addressdetails=1`,{headers:{'User-Agent':'CP-APPS/1.0'}}); if(!response.ok){throw new Error('Gagal mengambil alamat')} const data = await response.json(); return data.display_name || 'Alamat tidak ditemukan' } catch(error){console.warn('Error getting address:',error);return 'Alamat tidak dapat diambil'} }

        document.addEventListener('DOMContentLoaded', function() {
            window.questionsData = <?= json_encode($questions_kategori) ?>;
            
            // Inisialisasi field Peternak
            const jenisPeternakSelect = document.querySelector('div[data-field-name="jenis_peternak"] select');
            if (jenisPeternakSelect) {
                toggleDependentFields(jenisPeternakSelect.value);
            }
            
            // Inisialisasi field Pakan saat halaman dimuat
            const kategori = "<?= $kategori_selected ?>";
            if (kategori === 'Pakan') {
                const tipeTernakSelect = document.querySelector('div[data-field-name="tipe_ternak"] select');
                if (tipeTernakSelect) {
                    togglePakanFields(tipeTernakSelect.value);
                }
            }
            
            // Fungsi format angka
            function formatNumber(e) { let input = e.target; let value = input.value.replace(/,/g, ''); if (value.trim() === '') { input.value = ''; return; } let cursorPosition = input.selectionStart; const lengthBefore = input.value.length; input.value = new Intl.NumberFormat('en-US').format(value); const lengthAfter = input.value.length; cursorPosition += (lengthAfter - lengthBefore); input.setSelectionRange(cursorPosition, cursorPosition); }
            document.querySelectorAll('.numeric-input').forEach(input => { input.addEventListener('input', formatNumber); if (input.value) { formatNumber({ target: input }); } });

            // Handler untuk submit form dan lokasi
            const mainForm = document.getElementById('mainForm');
            const needsLocation = (kategori === 'Farm' || kategori === 'Sub Agen');
            let locationCaptured = false;
            
            mainForm.addEventListener('submit', async function(e) {
                document.querySelectorAll('.numeric-input').forEach(input => { input.value = input.value.replace(/,/g, ''); });
                if (!needsLocation || locationCaptured) return;
                e.preventDefault();
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'Mengambil Lokasi...';
                try {
                    const position = await getCurrentLocation();
                    const { latitude, longitude } = position.coords;
                    document.getElementById('latitude').value = parseFloat(latitude).toFixed(7);
                    document.getElementById('longitude').value = parseFloat(longitude).toFixed(7);
                    submitBtn.textContent = 'Mengambil Alamat...';
                    const address = await getAddressFromCoordinates(latitude, longitude);
                    document.getElementById('location_address').value = address;
                    locationCaptured = true;
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Mengirim Data...';
                    submitBtn.click();
                } catch (error) {
                    alert(error.message);
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            });
        });
    </script>
</body>
</html>
