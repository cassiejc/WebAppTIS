<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-widh, initial-scale=1">
    <title>Petelur</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    
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
        .selected-farm { background-color: #fefefeff; border-color: #dee2e6; }
        .integer-input, .currency-input, .varchar-input, .letters-only-input { position: relative; max-width: 400px; }
        .currency-prefix { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #666; pointer-events: none; }
        .currency-input input { padding-left: 35px; }
        .loading { display: none; color: #666; font-style: italic; }
        .form-check { padding-left: 2.5em; } /* Fix alignment for radio button */
        .form-check-input { float: left; margin-left: -2.5em; }
        
        /* Hide scrollbar for textarea and disable manual resize */
        textarea::-webkit-scrollbar {
            display: none;
        }
        textarea {
            resize: none !important; /* Force disable resize handle */
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <h2 class="page-title mb-4">Petelur - <?=$current_sub_area['nama_sub_area']?></h2>

        <form method="post" action="" id="petelurForm" class="form-container">
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
            
            <div class="loading" id="loading">Memuat pertanyaan...</div>
            
            <button type="submit" class="btn btn-primary px-4 py-2 mt-4" id="submit-btn">Submit</button>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
    
    function isLetterKey(evt) { const c = evt.keyCode; return (c >= 65 && c <= 90) || (c >= 97 && c <= 122) || c === 32 || c === 8 || (c >= 37 && c <= 40); }
    function isIntegerKey(evt) { const c = evt.keyCode; return (c >= 48 && c <= 57) || c === 8 || (c >= 37 && c <= 40); }
    function isNumberKey(evt) { 
        const c = evt.keyCode; 
        const input = evt.target;
        // Allow: backspace, delete, tab, escape, enter, arrow keys, and numbers
        if (c === 8 || c === 9 || c === 27 || c === 13 || (c >= 37 && c <= 40)) {
            return true;
        }
        // Allow numbers
        if (c >= 48 && c <= 57) {
            return true;
        }
        if (c === 46) {
            return input.value.indexOf('.') === -1;
        }
        return false;
    }
    
    function formatWithComma(input) { 
        let v = input.value.replace(/[^\d]/g, ''); 
        if (v) {
            input.value = parseInt(v, 10).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        } else {
            input.value = '';
        }
    }
    
    function shouldBeIntegerType(f) { return ['efektif_terisi_petelur', 'doa_woa_petelur', 'layer_pakai_pakan_cp', 'layer_selain_pakan_cp', 'layer_jumlah_kandang', 'layer_lama_puncak_produksi', 'layer_populasi', 'layer_woa', 'layer_umur_tertua', 'layer_umur_termuda', 'petelur_umur'].includes(f); }
    function shouldBeCurrencyType(f) { return ['harga_jual_telur_terakhir', 'layer_harga_jual_telur', 'layer_harga_beli_jagung', 'layer_harga_beli_katul', 'layer_harga_afkir'].includes(f); }
    function shouldHaveDecimalSupport(f) { return ['deplesi_petelur', 'intake_petelur', 'produksi_telur_petelur', 'berat_telur_petelur', 'fcr_petelur', 'layer_hen_day', 'layer_deplesi', 'layer_intake', 'layer_produksi_telur', 'layer_berat_telur', 'layer_fcr', 'suhu_kandang_layer', 'kelembapan_kandang_layer'].includes(f); }
    
    // Fungsi untuk auto resize textarea
    function autoResize(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    }
    
    // Fungsi baru untuk Dropdown Pencarian
    function toggleDropdown(qId) { const dd = document.getElementById(`dd_content_${qId}`); if (dd) dd.classList.toggle('show'); }
    function selectOption(qId, text) { document.getElementById(`dd_hidden_${qId}`).value = text; document.getElementById(`dd_btn_${qId}`).querySelector('span').textContent = text; document.getElementById(`dd_content_${qId}`).classList.remove('show'); }
    function filterOptions(qId) { const filter = document.getElementById(`dd_search_${qId}`).value.toUpperCase(); document.querySelectorAll(`#dd_content_${qId} .farm-option`).forEach(opt => { opt.style.display = opt.textContent.toUpperCase().includes(filter) ? '' : 'none'; }); }

    function changeTipeTermak(tipeTermak) {
        document.getElementById('selected_tipe_ternak').value = tipeTermak;
        const container = document.getElementById('questions-container');
        container.querySelectorAll('.question-group').forEach(g => { if (g.dataset.field !== 'tipe_ternak') g.remove(); });
        if (!tipeTermak) { enableDependentElements(false); return; }
        
        document.getElementById('loading').style.display = 'block';
        document.getElementById('submit-btn').disabled = true;
        
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '<?= base_url('Visiting_Petelur_Controller/get_questions_by_type') ?>', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    updateQuestionsDisplay(JSON.parse(xhr.responseText));
                } catch (e) {
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
    
    function updateQuestionsDisplay(questions) {
        const container = document.getElementById('questions-container');
        questions.filter(q => q.field_name !== 'tipe_ternak').forEach(q => {
            const el = createQuestionElement(q);
            if(el) container.appendChild(el);
        });
        enableDependentElements(true);
    }

    function createQuestionElement(q) {
        const formGroup = document.createElement('div');
        formGroup.className = 'mb-4 question-group';
        formGroup.dataset.field = q.field_name;

        formGroup.innerHTML = `<label class="form-label fw-bold">${q.question_text} ${(q.required && q.required !== '0' && q.required !== 0) ? '<span class="text-danger">*</span>' : ''}</label>`;
        const commonAttrs = `name="q${q.questions_id}" class="form-control" ${(q.required && q.required !== '0' && q.required !== 0) ? 'required' : ''} style="max-width:400px;"`;

        // Logika untuk menentukan tipe elemen form
        if (q.type === 'select' && q.options) {
            const isSearchable = q.field_name.includes('nama_farm') || q.field_name.includes('pakan');
            if (isSearchable) {
                const optionsHTML = q.options.map(opt => `<div class="farm-option" onclick="selectOption(${q.questions_id}, '${opt.option_text.replace(/'/g, "\\'")}')">${opt.option_text}</div>`).join('');
                formGroup.innerHTML += `
                    <div class="custom-dropdown" style="max-width: 400px;">
                        <input type="hidden" name="q${q.questions_id}" id="dd_hidden_${q.questions_id}" ${(q.required && q.required !== '0' && q.required !== 0) ? 'required' : ''}>
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
                    <input class="form-check-input" type="radio" name="q${q.questions_id}" value="${opt.option_text}" ${(q.required && q.required !== '0' && q.required !== 0) ? 'required' : ''}>
                    <label class="form-check-label">${opt.option_text}</label>
                </div>`).join('');
            formGroup.innerHTML += optionsHTML;
        } else if (q.type === 'date') {
            formGroup.innerHTML += `<input type="date" ${commonAttrs}>`;
        } else if (q.type === 'textarea') {
            // Fixed textarea - no manual resize, with auto-resize on input
            formGroup.innerHTML += `<textarea ${commonAttrs} rows="3" placeholder="Masukkan catatan..." style="resize: none !important; overflow: hidden; scrollbar-width: none; -ms-overflow-style: none;" oninput="autoResize(this)"></textarea>`;
        } else { // Handle 'text' dan 'number' dengan validasi spesifik
            let input;
            if (shouldBeCurrencyType(q.field_name)) {
                input = `<div class="currency-input"><span class="currency-prefix">Rp</span><input type="text" ${commonAttrs} placeholder="0" oninput="formatWithComma(this)" onkeypress="return isIntegerKey(event)"></div>`;
            } else if (shouldBeIntegerType(q.field_name)) {
                input = `<div class="integer-input"><input type="text" ${commonAttrs} placeholder="Masukkan angka bulat" oninput="formatWithComma(this)" onkeypress="return isIntegerKey(event)"></div>`;
            } else if (shouldHaveDecimalSupport(q.field_name)) {
                input = `<input type="text" inputmode="decimal" ${commonAttrs} placeholder="Masukkan angka" onkeypress="return isNumberKey(event)">`;
            } else {
                input = `<input type="text" ${commonAttrs} placeholder="Masukkan teks" onkeypress="return isLetterKey(event)">`;
            }
            formGroup.innerHTML += input;
        }
        return formGroup;
    }

    function enableDependentElements(enable) {
        document.querySelectorAll('.question-group').forEach(g => {
            if (g.dataset.field !== 'tipe_ternak') {
                g.querySelectorAll('input, select, button, textarea').forEach(el => {
                    el.disabled = !enable;
                    if (el.tagName === 'BUTTON') enable ? el.classList.remove('disabled') : el.classList.add('disabled');
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        enableDependentElements(false);
        document.addEventListener('click', e => { if (!e.target.closest('.custom-dropdown')) document.querySelectorAll('.dropdown-content.show').forEach(d => d.classList.remove('show')); });
        
        document.getElementById('petelurForm').addEventListener('submit', function(e) { 
            this.querySelectorAll('input[oninput*="Comma"]').forEach(i => { 
                i.value = i.value.replace(/,/g, ''); 
            }); 
        });
    });
    </script>
</body>
</html>
