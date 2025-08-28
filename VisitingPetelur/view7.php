<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Petelur</title>
    <style>
        form {
            margin-left: 20px;
        }

        h2 {
            margin-left: 10px;
        }

        .form-control {
            width: 400px;
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        input[type="date"] {
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
            width: 400px;
        }

        input[type="number"] {
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
            width: 400px;
        }

        .form-group {
            margin-bottom: 8px;
            max-width: 500px;
        }

        select.form-control {
            height: 35px;
        }

        .loading {
            display: none;
            color: #666;
            font-style: italic;
        }

        /* Dropdown styles tetap sama */
        .dropbtn {
            background-color: #ffffff;
            color: #333333;
            padding: 10px 16px;
            font-size: 14px;
            border: 1px solid #cccccc;
            cursor: pointer;
            width: 400px;
            text-align: left;
            border-radius: 4px;
        }

        .dropbtn:hover, .dropbtn:focus {
            background-color: #f5f5f5;
            border-color: #999999;
        }

        .dropbtn:disabled, .dropbtn.disabled {
            background-color: #f5f5f5;
            color: #999999;
            cursor: not-allowed;
            border-color: #cccccc;
        }

        .dropbtn:disabled:hover, .dropbtn.disabled:hover {
            background-color: #f5f5f5;
            border-color: #cccccc;
        }

        .dropdown-search-input {
            box-sizing: border-box;
            font-size: 14px;
            padding: 10px 15px;
            border: none;
            border-bottom: 1px solid #ddd;
            width: 100%;
        }

        .dropdown-search-input:focus {
            outline: 2px solid #666666;
        }

        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #ffffff;
            min-width: 400px;
            max-height: 200px;
            overflow: auto;
            border: 1px solid #cccccc;
            z-index: 1000;
            border-radius: 4px;
        }

        .dropdown-content a {
            color: #333333;
            padding: 10px 15px;
            text-decoration: none;
            display: block;
            cursor: pointer;
        }

        .dropdown-content a:hover {
            background-color: #f5f5f5;
        }

        .show {
            display: block;
        }

        input[type="submit"] {
            margin-top: 15px;
            padding: 8px 20px;
            background-color: #2f42beff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        select.form-control:disabled {
            background-color: #f5f5f5;
            color: #999999;
            cursor: not-allowed;
        }

        .currency-input {
            position: relative;
        }

        .currency-prefix {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            pointer-events: none;
        }

        .currency-input input {
            padding-left: 30px;
        }

        /* Style untuk integer fields dengan format ribuan */
        .integer-input {
            position: relative;
        }

        /* Style untuk varchar input */
        .varchar-input {
            position: relative;
        }

        /* Style untuk letters only input */
        .letters-only-input {
            position: relative;
        }
    </style>
</head>
<body>
    <h2>Petelur</h2>

    <form method="post" action="">
        <input type="hidden" id="selected_tipe_ternak" name="tipe_ternak" value="">
        
        <div id="questions-container">
            <!-- Questions will be loaded here dynamically -->
            <?php if (!empty($questions)): ?>
                <?php foreach ($questions as $q): ?>
                    <div class="form-group question-group">
                        <label>
                            <?= $q['question_text'] ?>
                            <?php if (!empty($q['required']) && $q['required'] == 1): ?> <span style="color: red">*</span> <?php endif; ?>
                        </label>
                        <br>
                        <?php if ($q['type'] === 'select' && !empty($q['options'])): ?>
                            <?php if ($q['field_name'] === 'nama_farm'): ?>
                                <div class="dropdown">
                                    <button onclick="toggleDropdown('dropdown_<?= $q['questions_id'] ?>')" 
                                            class="dropbtn <?php if ($q['field_name'] === 'nama_farm' || strpos($q['field_name'], 'strain') !== false): ?>disabled<?php endif; ?>" 
                                            type="button" 
                                            id="dropbtn_<?= $q['questions_id'] ?>"
                                            <?php if ($q['field_name'] === 'nama_farm' || strpos($q['field_name'], 'strain') !== false): ?>disabled<?php endif; ?>>-- Pilih Nama Farm --</button>
                                    <div id="dropdown_<?= $q['questions_id'] ?>" class="dropdown-content">
                                        <input type="text" 
                                               placeholder="Search..." 
                                               class="dropdown-search-input" 
                                               onkeyup="filterDropdown('dropdown_<?= $q['questions_id'] ?>')"
                                               onclick="event.stopPropagation();">
                                        <?php foreach ($q['options'] as $opt): ?>
                                            <a onclick="selectOption('<?= $q['questions_id'] ?>', '<?= htmlspecialchars($opt['option_text'], ENT_QUOTES) ?>')"><?= $opt['option_text'] ?></a>
                                        <?php endforeach; ?>
                                    </div>
                                    <input type="hidden" name="q<?= $q['questions_id'] ?>" id="hidden_<?= $q['questions_id'] ?>" <?= (!empty($q['required']) && $q['required'] == 1) ? 'required' : '' ?>>
                                </div>
                            <?php else: ?>
                                <select name="q<?= $q['questions_id'] ?>" class="form-control" 
                                        <?php if ($q['field_name'] === 'tipe_ternak'): ?>onchange="changeTipeTermak(this.value)"<?php endif; ?>
                                        <?php if (strpos($q['field_name'], 'strain') !== false): ?>disabled<?php endif; ?>
                                        <?= (!empty($q['required']) && $q['required'] == 1) ? 'required' : '' ?>>
                                    <option value="">-- Pilih --</option>
                                    <?php foreach ($q['options'] as $opt): ?>
                                        <option value="<?= $opt['option_text'] ?>">
                                            <?= $opt['option_text'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        
                        <?php elseif ($q['type'] === 'radio' && !empty($q['options'])): ?>
                            <?php foreach ($q['options'] as $opt): ?>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="q<?= $q['questions_id'] ?>" 
                                               value="<?= $opt['option_text'] ?>" <?= (!empty($q['required']) && $q['required'] == 1) ? 'required' : '' ?>>
                                        <?= $opt['option_text'] ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php elseif ($q['type'] === 'date'): ?>
                            <input type="date" name="q<?= $q['questions_id'] ?>" 
                                   class="form-control"
                                   <?= (!empty($q['required']) && $q['required'] == 1) ? 'required' : '' ?>>
                        <?php elseif ($q['type'] === 'text'): ?>
                            <?php if (isset($q['input_type']) && $q['input_type'] === 'integer'): ?>
                                <div class="integer-input">
                                    <input type="text" name="q<?= $q['questions_id'] ?>" 
                                           class="form-control integer-field"
                                           onkeypress="return isIntegerKey(event)"
                                           oninput="formatIntegerWithComma(this)"
                                           onpaste="handleIntegerPaste(event)"
                                           <?= (!empty($q['required']) && $q['required'] == 1) ? 'required' : '' ?>>
                                </div>
                            <?php elseif (isset($q['input_type']) && $q['input_type'] === 'currency'): ?>
                                <div class="currency-input">
                                    <span class="currency-prefix">Rp</span>
                                    <input type="text" name="q<?= $q['questions_id'] ?>" 
                                           class="form-control"
                                           oninput="formatCurrencyWithComma(this)"
                                           onkeypress="return isNumberKey(event)"
                                           <?= (!empty($q['required']) && $q['required'] == 1) ? 'required' : '' ?>>
                                </div>
                            <?php elseif (isset($q['input_type']) && $q['input_type'] === 'letters_only'): ?>
                                <div class="letters-only-input">
                                    <input type="text" name="q<?= $q['questions_id'] ?>" 
                                           class="form-control letters-only-field"
                                           onkeypress="return isLetterKey(event)"
                                           oninput="filterLettersOnly(this)"
                                           onpaste="handleLettersOnlyPaste(event)"
                                           <?= (!empty($q['required']) && $q['required'] == 1) ? 'required' : '' ?>>
                                </div>
                            <?php elseif (isset($q['input_type']) && $q['input_type'] === 'varchar'): ?>
                                <div class="varchar-input">
                                    <input type="text" name="q<?= $q['questions_id'] ?>" 
                                           class="form-control varchar-field"
                                           <?= (!empty($q['required']) && $q['required'] == 1) ? 'required' : '' ?>>
                                </div>
                            <?php else: ?>
                                <input type="text" name="q<?= $q['questions_id'] ?>" 
                                       class="form-control"
                                       <?= (!empty($q['required']) && $q['required'] == 1) ? 'required' : '' ?>>
                            <?php endif; ?>
                        <?php elseif ($q['type'] === 'number'): ?>
                            <?php if (isset($q['input_type']) && $q['input_type'] === 'integer'): ?>
                                <div class="integer-input">
                                    <input type="text" name="q<?= $q['questions_id'] ?>" 
                                           class="form-control integer-field"
                                           onkeypress="return isIntegerKey(event)"
                                           oninput="formatIntegerWithComma(this)"
                                           onpaste="handleIntegerPaste(event)"
                                           <?= (!empty($q['required']) && $q['required'] == 1) ? 'required' : '' ?>>
                                </div>
                            <?php else: ?>
                                <input type="number" name="q<?= $q['questions_id'] ?>" 
                                       class="form-control"
                                       step="<?= isset($q['step']) ? $q['step'] : '0.01' ?>"
                                       min="0"
                                       <?= (!empty($q['required']) && $q['required'] == 1) ? 'required' : '' ?>>
                            <?php endif; ?>
                        <?php elseif ($q['type'] === 'textarea'): ?>
                            <textarea name="q<?= $q['questions_id'] ?>" 
                                      class="form-control" rows="3"
                                      <?= (!empty($q['required']) && $q['required'] == 1) ? 'required' : '' ?>></textarea>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Tidak ada pertanyaan.</p>
            <?php endif; ?>
        </div>
        
        <div class="loading" id="loading">Memuat pertanyaan...</div>
        
        <input type="submit" value="Submit" id="submit-btn">
    </form>

    <script>
        // FUNGSI BARU: Untuk field letters only (hanya huruf)
        function isLetterKey(evt) {
            const charCode = (evt.which) ? evt.which : evt.keyCode;
            
            // Allow: backspace, delete, tab, escape, enter, space, and arrow keys
            if (charCode === 46 || charCode === 8 || charCode === 9 || 
                charCode === 27 || charCode === 13 || charCode === 32 ||
                (charCode >= 37 && charCode <= 40)) {
                return true;
            }
            
            // Only allow letters (A-Z, a-z)
            if ((charCode < 65 || charCode > 90) && (charCode < 97 || charCode > 122)) {
                evt.preventDefault();
                return false;
            }
            
            return true;
        }

        // FUNGSI BARU: Filter hanya huruf untuk letters only field
        function filterLettersOnly(input) {
            // Hapus semua karakter non-huruf kecuali spasi
            let value = input.value.replace(/[^a-zA-Z\s]/g, '');
            
            // Trim extra spaces
            value = value.replace(/\s+/g, ' ').trim();
            
            input.value = value;
        }

        // FUNGSI BARU: Handle paste untuk field letters only
        function handleLettersOnlyPaste(evt) {
            evt.preventDefault();
            
            // Ambil data yang dipaste
            let paste = (evt.clipboardData || window.clipboardData).getData('text');
            
            // Hapus semua karakter non-huruf kecuali spasi
            let cleanValue = paste.replace(/[^a-zA-Z\s]/g, '');
            
            // Trim extra spaces
            cleanValue = cleanValue.replace(/\s+/g, ' ').trim();
            
            evt.target.value = cleanValue;
        }

        // FUNGSI BARU: Untuk field integer dengan format ribuan (koma)
        function isIntegerKey(evt) {
            const charCode = (evt.which) ? evt.which : evt.keyCode;
            
            // Allow: backspace, delete, tab, escape, enter, and arrow keys
            if (charCode === 46 || charCode === 8 || charCode === 9 || 
                charCode === 27 || charCode === 13 || 
                (charCode >= 37 && charCode <= 40)) {
                return true;
            }
            
            // Only allow digits (0-9)
            if (charCode < 48 || charCode > 57) {
                evt.preventDefault();
                return false;
            }
            
            return true;
        }

        // FUNGSI BARU: Format integer dengan koma sebagai pemisah ribuan
        function formatIntegerWithComma(input) {
            // Hapus semua karakter non-digit
            let value = input.value.replace(/[^\d]/g, '');
            
            if (value === '') {
                input.value = '';
                return;
            }
            
            // Konversi ke integer dan format dengan koma
            let formatted = parseInt(value, 10).toLocaleString('en-US');
            input.value = formatted;
        }

        // FUNGSI BARU: Handle paste untuk field integer
        function handleIntegerPaste(evt) {
            evt.preventDefault();
            
            // Ambil data yang dipaste
            let paste = (evt.clipboardData || window.clipboardData).getData('text');
            
            // Hapus semua karakter non-digit
            let cleanValue = paste.replace(/[^\d]/g, '');
            
            if (cleanValue !== '') {
                // Format dengan koma dan masukkan ke input
                let formatted = parseInt(cleanValue, 10).toLocaleString('en-US');
                evt.target.value = formatted;
            }
        }

        // PERBAIKAN: Fungsi untuk mencegah input desimal pada field integer (untuk backward compatibility)
        function preventNonIntegerInput(evt) {
            return isIntegerKey(evt);
        }

        // Function to format currency input with comma as thousands separator
        function formatCurrencyWithComma(input) {
            let value = input.value.replace(/[^\d]/g, '');
            
            if (value === '') {
                input.value = '';
                return;
            }
            
            let formatted = parseInt(value).toLocaleString('en-US');
            input.value = formatted;
        }

        // Function to allow only numbers and backspace for currency input
        function isNumberKey(evt) {
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            if (charCode == 46 || charCode == 8 || charCode == 9 || charCode == 27 || charCode == 13) {
                return true;
            }
            if ((charCode < 48 || charCode > 57)) {
                return false;
            }
            return true;
        }

        // Function to check if field should be integer type
        function shouldBeIntegerType(fieldName) {
            const integerFields = [
                'efektif_terisi_petelur', 
                'doa_woa_petelur', 
                'harga_jual_telur_terakhir',
                'layer_pakai_pakan_cp',
                'layer_selain_pakan_cp',
                'layer_jumlah_kandang',
                'layer_lama_puncak_produksi',
                'layer_populasi',
                'layer_woa',
                'layer_harga_jual_telur',
                'layer_harga_beli_jagung',
                'layer_harga_beli_katul',
                'layer_harga_afkir',
                'layer_umur_tertua',
                'layer_umur_termuda'
            ];
            return integerFields.includes(fieldName);
        }

        // Function to check if field should be number type
        function shouldBeNumberType(fieldName) {
            const numberFields = [
                'efektif_terisi_petelur', 
                'doa_woa_petelur', 
                'deplesi_petelur', 
                'intake_petelur', 
                'produksi_telur_petelur', 
                'berat_telur_petelur', 
                'fcr_petelur',
                'harga_jual_telur_terakhir',
                'layer_pakai_pakan_cp',
                'layer_selain_pakan_cp',
                'layer_jumlah_kandang',
                'layer_hen_day',
                'layer_lama_puncak_produksi',
                'layer_deplesi',
                'layer_intake',
                'layer_produksi_telur',
                'layer_berat_telur',
                'layer_fcr',
                'layer_harga_jual_telur',
                'layer_harga_beli_jagung',
                'layer_harga_beli_katul',
                'layer_harga_afkir',
                'layer_umur_tertua',
                'layer_umur_termuda',
                'suhu_kandang_layer',
                'kelembapan_kandang_layer'
            ];
            return numberFields.includes(fieldName);
        }

        // Function to check if field should be currency type
        function shouldBeCurrencyType(fieldName) {
            const currencyFields = [
                'harga_jual_telur_terakhir',
                'layer_harga_jual_telur',
                'layer_harga_beli_jagung',
                'layer_harga_beli_katul',
                'layer_harga_afkir'
            ];
            return currencyFields.includes(fieldName);
        }

        // Function to check if field should be letters only type
        function shouldBeLettersOnlyType(fieldName) {
            const lettersOnlyFields = [
                
            ];
            return lettersOnlyFields.includes(fieldName);
        }

        // Function to check if field should be varchar type
        function shouldBeVarcharType(fieldName) {
            const varcharFields = [
                'layer_kode_label_pakan',
                'layer_nama_kandang'
            ];
            return varcharFields.includes(fieldName);
        }

        // Function to check if field should have decimal support
        function shouldHaveDecimalSupport(fieldName) {
            const decimalFields = [
                'deplesi_petelur', 
                'intake_petelur', 
                'produksi_telur_petelur', 
                'berat_telur_petelur', 
                'fcr_petelur',
                'layer_hen_day',
                'layer_deplesi',
                'layer_intake',
                'layer_produksi_telur',
                'layer_berat_telur',
                'layer_fcr',
                'suhu_kandang_layer',
                'kelembapan_kandang_layer'
            ];
            return decimalFields.includes(fieldName);
        }

        // Toggle dropdown visibility
        function toggleDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            const button = dropdown.previousElementSibling;
            
            if (button.disabled || button.classList.contains('disabled')) {
                return;
            }
            
            dropdown.classList.toggle("show");
        }

        // Filter dropdown options based on search input
        function filterDropdown(dropdownId) {
            const input = document.querySelector(`#${dropdownId} .dropdown-search-input`);
            const filter = input.value.toUpperCase();
            const div = document.getElementById(dropdownId);
            const a = div.getElementsByTagName("a");
            
            for (let i = 0; i < a.length; i++) {
                const txtValue = a[i].textContent || a[i].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    a[i].style.display = "";
                } else {
                    a[i].style.display = "none";
                }
            }
        }

        // Select option and update button text and hidden input
        function selectOption(questionId, optionText) {
            document.getElementById(`dropbtn_${questionId}`).textContent = optionText;
            document.getElementById(`hidden_${questionId}`).value = optionText;
            document.getElementById(`dropdown_${questionId}`).classList.remove("show");
        }

        // Close dropdown when clicking outside
        window.onclick = function(event) {
            if (!event.target.matches('.dropbtn')) {
                const dropdowns = document.getElementsByClassName("dropdown-content");
                for (let i = 0; i < dropdowns.length; i++) {
                    const openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }

        // Function to enable/disable dependent dropdowns
        function enableDependentDropdowns(enable) {
            const namaFarmButtons = document.querySelectorAll('[id^="dropbtn_"]');
            namaFarmButtons.forEach(button => {
                const questionGroup = button.closest('.form-group');
                const label = questionGroup.querySelector('label');
                
                if (label && (label.textContent.includes('Nama Farm') || label.textContent.includes('nama_farm'))) {
                    if (enable) {
                        button.disabled = false;
                        button.classList.remove('disabled');
                    } else {
                        button.disabled = true;
                        button.classList.add('disabled');
                        button.textContent = '-- Pilih Nama Farm --';
                        const hiddenInput = questionGroup.querySelector('input[type="hidden"]');
                        if (hiddenInput) hiddenInput.value = '';
                    }
                }
            });

            const strainSelects = document.querySelectorAll('select[class*="form-control"]');
            strainSelects.forEach(select => {
                const questionGroup = select.closest('.form-group');
                const label = questionGroup.querySelector('label');
                
                if (label && (label.textContent.toLowerCase().includes('strain') || select.name.includes('strain'))) {
                    if (enable) {
                        select.disabled = false;
                    } else {
                        select.disabled = true;
                        select.value = '';
                    }
                }
            });
        }

        // Function for handling tipe_ternak change
        function changeTipeTermak(tipeTermak) {
            document.getElementById('selected_tipe_ternak').value = tipeTermak;
            
            if (tipeTermak === '') {
                enableDependentDropdowns(false);
                return;
            }
            
            enableDependentDropdowns(true);
            
            document.getElementById('loading').style.display = 'block';
            document.getElementById('submit-btn').disabled = true;
            
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '<?= base_url('Visiting_Petelur_Controller/get_questions_by_type') ?>', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        var questions = JSON.parse(xhr.responseText);
                        updateQuestionsDisplay(questions);
                        
                        document.getElementById('loading').style.display = 'none';
                        document.getElementById('submit-btn').disabled = false;
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        document.getElementById('loading').style.display = 'none';
                        document.getElementById('submit-btn').disabled = false;
                    }
                }
            };
            
            xhr.send('tipe_ternak=' + encodeURIComponent(tipeTermak));
        }
        
        function updateQuestionsDisplay(questions) {
            var container = document.getElementById('questions-container');
            container.innerHTML = '';
            
            if (questions && questions.length > 0) {
                questions.forEach(function(q) {
                    var formGroup = document.createElement('div');
                    formGroup.className = 'form-group question-group';
                    
                    var label = document.createElement('label');
                    label.innerHTML = q.question_text;
                    if (q.required == 1) {
                        label.innerHTML += ' <span style="color: red">*</span>';
                    }
                    
                    var br = document.createElement('br');
                    
                    formGroup.appendChild(label);
                    formGroup.appendChild(br);
                    
                    if (q.type === 'select' && q.options && q.options.length > 0) {
                        if (q.field_name === 'nama_farm' || q.field_name === 'layer_nama_farm') {
                            var dropdown = document.createElement('div');
                            dropdown.className = 'dropdown';
                            
                            var dropbtn = document.createElement('button');
                            dropbtn.type = 'button';
                            dropbtn.className = 'dropbtn';
                            dropbtn.id = 'dropbtn_' + q.questions_id;
                            dropbtn.textContent = '-- Pilih Nama Farm --';
                            dropbtn.onclick = function() {
                                toggleDropdown('dropdown_' + q.questions_id);
                            };
                            
                            var dropdownContent = document.createElement('div');
                            dropdownContent.id = 'dropdown_' + q.questions_id;
                            dropdownContent.className = 'dropdown-content';
                            
                            var searchInput = document.createElement('input');
                            searchInput.type = 'text';
                            searchInput.placeholder = 'Search...';
                            searchInput.className = 'dropdown-search-input';
                            searchInput.onkeyup = function() {
                                filterDropdown('dropdown_' + q.questions_id);
                            };
                            searchInput.onclick = function(event) {
                                event.stopPropagation();
                            };
                            
                            dropdownContent.appendChild(searchInput);
                            
                            q.options.forEach(function(opt) {
                                var a = document.createElement('a');
                                a.textContent = opt.option_text;
                                a.onclick = function() {
                                    selectOption(q.questions_id, opt.option_text);
                                };
                                dropdownContent.appendChild(a);
                            });
                            
                            var hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = 'q' + q.questions_id;
                            hiddenInput.id = 'hidden_' + q.questions_id;
                            if (q.required == 1) hiddenInput.required = true;
                            
                            dropdown.appendChild(dropbtn);
                            dropdown.appendChild(dropdownContent);
                            formGroup.appendChild(dropdown);
                            formGroup.appendChild(hiddenInput);
                            
                        } else {
                            var select = document.createElement('select');
                            select.name = 'q' + q.questions_id;
                            select.className = 'form-control';
                            
                            if (q.field_name === 'tipe_ternak') {
                                select.onchange = function() {
                                    changeTipeTermak(this.value);
                                };
                            }
                            
                            if (q.field_name.toLowerCase().includes('strain')) {
                                select.disabled = true;
                            }
                            
                            if (q.required == 1) select.required = true;
                            
                            var defaultOption = document.createElement('option');
                            defaultOption.value = '';
                            defaultOption.textContent = '-- Pilih --';
                            select.appendChild(defaultOption);
                            
                            q.options.forEach(function(opt) {
                                var option = document.createElement('option');
                                option.value = opt.option_text;
                                option.textContent = opt.option_text;
                                
                                if (q.field_name === 'tipe_ternak' && opt.option_text === document.getElementById('selected_tipe_ternak').value) {
                                    option.selected = true;
                                }
                                
                                select.appendChild(option);
                            });
                            
                            formGroup.appendChild(select);
                        }
                        
                    } else if (q.type === 'radio' && q.options && q.options.length > 0) {
                        q.options.forEach(function(opt) {
                            var radioDiv = document.createElement('div');
                            radioDiv.className = 'radio';
                            
                            var radioLabel = document.createElement('label');
                            var radioInput = document.createElement('input');
                            radioInput.type = 'radio';
                            radioInput.name = 'q' + q.questions_id;
                            radioInput.value = opt.option_text;
                            if (q.required == 1) radioInput.required = true;
                            
                            radioLabel.appendChild(radioInput);
                            radioLabel.appendChild(document.createTextNode(' ' + opt.option_text));
                            radioDiv.appendChild(radioLabel);
                            formGroup.appendChild(radioDiv);
                        });
                        
                    } else if (q.type === 'date') {
                        var input = document.createElement('input');
                        input.type = 'date';
                        input.name = 'q' + q.questions_id;
                        input.className = 'form-control';
                        if (q.required == 1) input.required = true;
                        formGroup.appendChild(input);
                        
                    } else if (q.type === 'text') {
                        if (q.input_type === 'integer') {
                            var integerDiv = document.createElement('div');
                            integerDiv.className = 'integer-input';
                            
                            var input = document.createElement('input');
                            input.type = 'text';
                            input.name = 'q' + q.questions_id;
                            input.className = 'form-control integer-field';
                            input.onkeypress = function(event) {
                                return isIntegerKey(event);
                            };
                            input.oninput = function() {
                                formatIntegerWithComma(this);
                            };
                            input.onpaste = function(event) {
                                handleIntegerPaste(event);
                            };
                            if (q.required == 1) input.required = true;
                            
                            integerDiv.appendChild(input);
                            formGroup.appendChild(integerDiv);
                            
                        } else if (q.input_type === 'number') {
                            var input = document.createElement('input');
                            input.type = 'number';
                            input.name = 'q' + q.questions_id;
                            input.className = 'form-control';
                            input.step = q.step || '0.01';
                            input.min = '0';
                            if (q.required == 1) input.required = true;
                            formGroup.appendChild(input);
                            
                        } else if (q.input_type === 'currency') {
                            var currencyDiv = document.createElement('div');
                            currencyDiv.className = 'currency-input';
                            
                            var currencyPrefix = document.createElement('span');
                            currencyPrefix.className = 'currency-prefix';
                            currencyPrefix.textContent = 'Rp';
                            
                            var input = document.createElement('input');
                            input.type = 'text';
                            input.name = 'q' + q.questions_id;
                            input.className = 'form-control';
                            input.oninput = function() {
                                formatCurrencyWithComma(this);
                            };
                            input.onkeypress = function(event) {
                                return isNumberKey(event);
                            };
                            if (q.required == 1) input.required = true;
                            
                            currencyDiv.appendChild(currencyPrefix);
                            currencyDiv.appendChild(input);
                            formGroup.appendChild(currencyDiv);
                            
                        } else if (q.input_type === 'letters_only') {
                            var lettersDiv = document.createElement('div');
                            lettersDiv.className = 'letters-only-input';
                            
                            var input = document.createElement('input');
                            input.type = 'text';
                            input.name = 'q' + q.questions_id;
                            input.className = 'form-control letters-only-field';
                            input.onkeypress = function(event) {
                                return isLetterKey(event);
                            };
                            input.oninput = function() {
                                filterLettersOnly(this);
                            };
                            input.onpaste = function(event) {
                                handleLettersOnlyPaste(event);
                            };
                            if (q.required == 1) input.required = true;
                            
                            lettersDiv.appendChild(input);
                            formGroup.appendChild(lettersDiv);
                            
                        } else if (q.input_type === 'varchar') {
                            var varcharDiv = document.createElement('div');
                            varcharDiv.className = 'varchar-input';
                            
                            var input = document.createElement('input');
                            input.type = 'text';
                            input.name = 'q' + q.questions_id;
                            input.className = 'form-control varchar-field';
                            if (q.required == 1) input.required = true;
                            
                            varcharDiv.appendChild(input);
                            formGroup.appendChild(varcharDiv);
                            
                        } else {
                            var input = document.createElement('input');
                            input.type = 'text';
                            input.name = 'q' + q.questions_id;
                            input.className = 'form-control';
                            if (q.required == 1) input.required = true;
                            formGroup.appendChild(input);
                        }
                        
                    } else if (q.type === 'number') {
                        if (q.input_type === 'integer') {
                            var integerDiv = document.createElement('div');
                            integerDiv.className = 'integer-input';
                            
                            var input = document.createElement('input');
                            input.type = 'text';
                            input.name = 'q' + q.questions_id;
                            input.className = 'form-control integer-field';
                            input.onkeypress = function(event) {
                                return isIntegerKey(event);
                            };
                            input.oninput = function() {
                                formatIntegerWithComma(this);
                            };
                            input.onpaste = function(event) {
                                handleIntegerPaste(event);
                            };
                            if (q.required == 1) input.required = true;
                            
                            integerDiv.appendChild(input);
                            formGroup.appendChild(integerDiv);
                            
                        } else {
                            var input = document.createElement('input');
                            input.type = 'number';
                            input.name = 'q' + q.questions_id;
                            input.className = 'form-control';
                            input.step = q.step || '0.01';
                            input.min = '0';
                            if (q.required == 1) input.required = true;
                            formGroup.appendChild(input);
                        }
                        
                    } else if (q.type === 'textarea') {
                        var textarea = document.createElement('textarea');
                        textarea.name = 'q' + q.questions_id;
                        textarea.className = 'form-control';
                        textarea.rows = 3;
                        if (q.required == 1) textarea.required = true;
                        formGroup.appendChild(textarea);
                    }
                    
                    container.appendChild(formGroup);
                });
                
                enableDependentDropdowns(true);
            } else {
                container.innerHTML = '<p>Tidak ada pertanyaan.</p>';
            }
        }

        // Initialize page: disable dependent dropdowns on page load
        document.addEventListener('DOMContentLoaded', function() {
            enableDependentDropdowns(false);
        });
    </script>

</body>
</html> 
                                
