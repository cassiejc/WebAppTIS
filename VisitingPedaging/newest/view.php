<!DOCTYPE html>
<html>
<head>
    <title>Pedaging</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-container { margin-left: 20px; }
        .page-title { margin-left: 10px; }
        
        .auto-resize-textarea { 
            resize: none;
            min-height: 80px;
            overflow: hidden;
            line-height: 1.4;
        }
        
        /* Custom dropdown styles for nama farm filter */
        .custom-dropdown { position: relative; }
        
        .dropdown-toggle {
            background-color: white;
            color: #333;
            border: 1px solid #dee2e6;
            cursor: pointer;
            text-align: left;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .dropdown-toggle:hover, .dropdown-toggle:focus {
            background-color: #f8f9fa;
            border-color: #0d6efd;
        }

        .dropdown-toggle:disabled, .dropdown-toggle.disabled {
            background-color: #e9ecef;
            color: #6c757d;
            cursor: not-allowed;
            border-color: #dee2e6;
        }
        
        .dropdown-toggle::after {
            content: "▼";
            font-size: 12px;
        }
        
        .farm-search-input {
            border: none;
            border-bottom: 1px solid #dee2e6;
            background-color: #f8f9fa;
        }
        
        .farm-search-input:focus {
            outline: 2px solid #0d6efd;
            background-color: white;
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: white;
            min-width: 100%;
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 0 0 0.375rem 0.375rem;
            border-top: none;
            z-index: 1000;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
        }
        
        .dropdown-content .farm-option {
            color: #333;
            padding: 10px 12px;
            text-decoration: none;
            display: block;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        
        .dropdown-content .farm-option:hover { background-color: #f8f9fa; }
        .dropdown-content .farm-option:last-child { border-bottom: none; }
        .show { display: block; }
        .selected-farm { background-color: #fefefeff; border-color: #dee2e6; }

        /* Style untuk integer fields dengan format ribuan */
        .integer-input {
            position: relative;
            max-width: 400px;
        }

        /* Style untuk currency input */
        .currency-input {
            position: relative;
            max-width: 400px;
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

        /* Style untuk varchar input */
        .varchar-input {
            position: relative;
            max-width: 400px;
        }

        /* Style untuk letters only input */
        .letters-only-input {
            position: relative;
            max-width: 400px;
        }

        .loading {
            display: none;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <h2 class="page-title mb-4">Pedaging - <?php echo $current_sub_area['nama_sub_area']; ?></h2>

        <form method="post" action="" id="pulletForm" class="form-container">
            <input type="hidden" id="selected_tipe_ternak" name="tipe_ternak" value="">
            
            <div id="pulletQuestions">
                <?php if (!empty($questions)): ?>
                    <?php
                    // Separate jenis ternak question from others
                    $jenis_ternak_q = null;
                    $other_questions = [];
                    
                    foreach ($questions as $q) {
                        if (trim(strtolower($q['question_text'])) === 'jenis ternak pedaging') {
                            $jenis_ternak_q = $q;
                        } else {
                            $other_questions[] = $q;
                        }
                    }
                    ?>

                    <!-- Display "Jenis Ternak Pedaging" first -->
                    <?php if ($jenis_ternak_q): ?>
                        <div class="mb-4 question-group" data-field="<?= $jenis_ternak_q['field_name'] ?>">
                            <label class="form-label fw-bold">
                                <?= $jenis_ternak_q['question_text'] ?>
                                <?php if (!empty($jenis_ternak_q['required'])): ?> 
                                    <span class="text-danger">*</span> 
                                <?php endif; ?>
                            </label>
                            
                            <?php if ($jenis_ternak_q['type'] == 'select' && !empty($jenis_ternak_q['options'])): ?>
                                <select name="q<?= $jenis_ternak_q['questions_id'] ?>" 
                                        data-field="<?= $jenis_ternak_q['field_name'] ?>"
                                        class="form-select"
                                        style="max-width: 400px;"
                                        onchange="changeTipeTermak(this.value)"
                                        <?= !empty($jenis_ternak_q['required']) ? 'required' : '' ?>>
                                    <option value="">-- Pilih Jawaban --</option>
                                    <?php foreach ($jenis_ternak_q['options'] as $opt): ?>
                                        <option value="<?= $opt['option_text'] ?>" 
                                                data-tipe="<?= $opt['tipe_ternak'] ?? '' ?>" 
                                                class="option-item">
                                            <?= $opt['option_text'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Render remaining questions in database order -->
                    <?php foreach ($other_questions as $q): ?>
                        <div class="mb-4 question-group" data-field="<?= $q['field_name'] ?>">
                            <label class="form-label fw-bold">
                                <?= $q['question_text'] ?>
                                <?php if (!empty($q['required'])): ?> 
                                    <span class="text-danger">*</span> 
                                <?php endif; ?>
                            </label>
                            
                            <?php if ($q['type'] == 'select' && !empty($q['options'])): ?>
                                <?php if ($q['field_name'] === 'nama_farm'): ?>
                                    <!-- Custom Searchable Dropdown for Farm -->
                                    <div class="custom-dropdown" style="max-width: 400px;">
                                        <input type="hidden" 
                                               name="q<?= $q['questions_id'] ?>" 
                                               id="namaFarmHidden_<?= $q['questions_id'] ?>"
                                               data-field="<?= $q['field_name'] ?>"
                                               <?= !empty($q['required']) ? 'required' : '' ?>>
                                        
                                        <button type="button" 
                                                onclick="toggleFarmDropdown('<?= $q['questions_id'] ?>')" 
                                                class="btn dropdown-toggle w-100 disabled" 
                                                id="namaFarmToggle_<?= $q['questions_id'] ?>" 
                                                disabled>
                                            <span id="selectedFarmText_<?= $q['questions_id'] ?>">-- Pilih Nama Farm --</span>
                                        </button>
                                        
                                        <div id="namaFarmDropdown_<?= $q['questions_id'] ?>" class="dropdown-content w-100">
                                            <input type="text" 
                                                   placeholder="Cari nama farm..." 
                                                   id="farmSearchInput_<?= $q['questions_id'] ?>" 
                                                   class="form-control farm-search-input"
                                                   onkeyup="filterFarmOptions('<?= $q['questions_id'] ?>')">
                                            
                                            <?php if (!empty($q['options'])): ?>
                                                <?php foreach ($q['options'] as $opt): ?>
                                                    <div class="farm-option option-item" 
                                                         data-value="<?= $opt['option_text'] ?>"
                                                         data-tipe="<?= $opt['tipe_ternak'] ?? '' ?>"
                                                         onclick="selectFarmOption('<?= $q['questions_id'] ?>', '<?= $opt['option_text'] ?>')">
                                                        <?= $opt['option_text'] ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- Regular Select Dropdown -->
                                    <select name="q<?= $q['questions_id'] ?>" 
                                            data-field="<?= $q['field_name'] ?>"
                                            class="form-select"
                                            style="max-width: 400px;"
                                            <?php if (trim(strtolower($q['question_text'])) === 'jenis ternak pedaging'): ?>
                                                onchange="changeTipeTermak(this.value)"
                                            <?php elseif ($q['field_name'] === 'nama_peternak'): ?>
                                                disabled
                                            <?php endif; ?>
                                            <?= !empty($q['required']) ? 'required' : '' ?>>
                                        <option value="">-- Pilih Jawaban --</option>
                                        <?php foreach ($q['options'] as $opt): ?>
                                            <option value="<?= $opt['option_text'] ?>" 
                                                    data-tipe="<?= $opt['tipe_ternak'] ?? '' ?>" 
                                                    class="option-item">
                                                <?= $opt['option_text'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php endif; ?>

                            <?php elseif ($q['type'] == 'radio' && !empty($q['options'])): ?>
                                <div class="mt-2">
                                    <?php foreach ($q['options'] as $opt): ?>
                                        <div class="form-check option-item" data-tipe="<?= $opt['tipe_ternak'] ?? '' ?>">
                                            <input class="form-check-input" 
                                                   type="radio" 
                                                   name="q<?= $q['questions_id'] ?>" 
                                                   value="<?= $opt['option_text'] ?>"
                                                   data-field="<?= $q['field_name'] ?>"
                                                   data-tipe="<?= $opt['tipe_ternak'] ?? '' ?>"
                                                   id="radio_<?= $q['questions_id'] ?>_<?= $opt['options_id'] ?? rand() ?>"
                                                   <?= !empty($q['required']) ? 'required' : '' ?>>
                                            <label class="form-check-label" for="radio_<?= $q['questions_id'] ?>_<?= $opt['options_id'] ?? rand() ?>">
                                                <?= $opt['option_text'] ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                            <?php elseif ($q['type'] == 'textarea'): ?>
                                <textarea name="q<?= $q['questions_id'] ?>" 
                                          class="form-control auto-resize-textarea"
                                          data-field="<?= $q['field_name'] ?>"
                                          placeholder="Masukkan jawaban Anda"
                                          style="max-width: 400px;"
                                          <?= !empty($q['required']) ? 'required' : '' ?>></textarea>

                            <?php elseif ($q['type'] == 'text'): ?>
                                <?php if (isset($q['input_type']) && $q['input_type'] === 'integer'): ?>
                                    <div class="integer-input">
                                        <input type="text" 
                                               name="q<?= $q['questions_id'] ?>" 
                                               data-field="<?= $q['field_name'] ?>"
                                               class="form-control integer-field"
                                               onkeypress="return isIntegerKey(event)"
                                               oninput="formatIntegerWithComma(this)"
                                               onpaste="handleIntegerPaste(event)"
                                               placeholder="Masukkan angka bulat"
                                               style="max-width: 400px;"
                                               <?= !empty($q['required']) ? 'required' : '' ?>>
                                    </div>
                                <?php elseif (isset($q['input_type']) && $q['input_type'] === 'currency'): ?>
                                    <div class="currency-input">
                                        <span class="currency-prefix">Rp</span>
                                        <input type="text" 
                                               name="q<?= $q['questions_id'] ?>" 
                                               data-field="<?= $q['field_name'] ?>"
                                               class="form-control"
                                               oninput="formatCurrencyWithComma(this)"
                                               onkeypress="return isNumberKey(event)"
                                               placeholder=" 0"
                                               style="max-width: 400px;"
                                               <?= !empty($q['required']) ? 'required' : '' ?>>
                                    </div>
                                <?php elseif (isset($q['input_type']) && $q['input_type'] === 'letters_only'): ?>
                                    <div class="letters-only-input">
                                        <input type="text" 
                                               name="q<?= $q['questions_id'] ?>" 
                                               data-field="<?= $q['field_name'] ?>"
                                               class="form-control letters-only-field"
                                               onkeypress="return isLetterKey(event)"
                                               oninput="filterLettersOnly(this)"
                                               onpaste="handleLettersOnlyPaste(event)"
                                               style="max-width: 400px;"
                                               <?= !empty($q['required']) ? 'required' : '' ?>>
                                    </div>
                                <?php elseif (isset($q['input_type']) && $q['input_type'] === 'varchar'): ?>
                                    <div class="varchar-input">
                                        <input type="text" 
                                               name="q<?= $q['questions_id'] ?>" 
                                               data-field="<?= $q['field_name'] ?>"
                                               class="form-control varchar-field"
                                               style="max-width: 400px;"
                                               <?= !empty($q['required']) ? 'required' : '' ?>>
                                    </div>
                                <?php elseif ($q['field_name'] === 'efektif_terisi_pedaging'): ?>
                                    <input type="text" 
                                           name="q<?= $q['questions_id'] ?>" 
                                           data-field="<?= $q['field_name'] ?>"
                                           class="form-control number-format"
                                           pattern="[0-9,]*"
                                           inputmode="numeric"
                                           onkeypress="return (event.charCode >= 48 && event.charCode <= 57)"
                                           placeholder="Masukkan angka bulat"
                                           style="max-width: 400px;"
                                           oninput="formatNumber(this)"
                                           onblur="handleBlur(this)"
                                           <?= !empty($q['required']) ? 'required' : '' ?>>
                                <?php elseif ($q['field_name'] === 'umur_pedaging'): ?>
                                    <input type="number" 
                                           name="q<?= $q['questions_id'] ?>" 
                                           data-field="<?= $q['field_name'] ?>"
                                           class="form-control"
                                           step="1"
                                           min="0"
                                           onkeydown="return event.keyCode === 8 || event.keyCode === 46 ? true : !isNaN(Number(event.key)) && event.key !== 'e' && event.key !== '.'"
                                           placeholder="Masukkan angka bulat"
                                           style="max-width: 400px;"
                                           <?= !empty($q['required']) ? 'required' : '' ?>>
                                <?php elseif (in_array($q['field_name'], ['deplesi_pedaging', 'intake_pedaging', 'pencapaian_berat_pedaging', 'keseragaman_pedaging', 'fcr_pedaging'])): ?>
                                    <input type="number" 
                                           name="q<?= $q['questions_id'] ?>" 
                                           data-field="<?= $q['field_name'] ?>"
                                           class="form-control"
                                           step="0.01"
                                           min="0"
                                           placeholder="Masukkan angka"
                                           style="max-width: 400px;"
                                           <?= !empty($q['required']) ? 'required' : '' ?>>
                                <?php else: ?>
                                    <input type="text" 
                                           name="q<?= $q['questions_id'] ?>" 
                                           data-field="<?= $q['field_name'] ?>"
                                           class="form-control"
                                           placeholder="Masukkan jawaban Anda"
                                           style="max-width: 400px;"
                                           <?= !empty($q['required']) ? 'required' : '' ?>>
                                <?php endif; ?>

                            <?php elseif ($q['type'] == 'number'): ?>
                                <?php if (isset($q['input_type']) && $q['input_type'] === 'integer'): ?>
                                    <div class="integer-input">
                                        <input type="text" 
                                               name="q<?= $q['questions_id'] ?>" 
                                               data-field="<?= $q['field_name'] ?>"
                                               class="form-control integer-field"
                                               onkeypress="return isIntegerKey(event)"
                                               oninput="formatIntegerWithComma(this)"
                                               onpaste="handleIntegerPaste(event)"
                                               placeholder="Masukkan angka bulat"
                                               style="max-width: 400px;"
                                               <?= !empty($q['required']) ? 'required' : '' ?>>
                                    </div>
                                <?php else: ?>
                                    <input type="number" 
                                           name="q<?= $q['questions_id'] ?>" 
                                           data-field="<?= $q['field_name'] ?>"
                                           class="form-control"
                                           step="<?= isset($q['step']) ? $q['step'] : '0.01' ?>"
                                           min="0"
                                           placeholder="Masukkan angka"
                                           style="max-width: 400px;"
                                           <?= !empty($q['required']) ? 'required' : '' ?>>
                                <?php endif; ?>

                            <?php elseif ($q['type'] == 'date'): ?>
                                <input type="date" 
                                       name="q<?= $q['questions_id'] ?>" 
                                       data-field="<?= $q['field_name'] ?>"
                                       class="form-control"
                                       style="max-width: 400px;"
                                       <?= !empty($q['required']) ? 'required' : '' ?>>

                            <?php elseif ($q['type'] == 'checkbox' && !empty($q['options'])): ?>
                                <div class="mt-2">
                                    <?php foreach ($q['options'] as $opt): ?>
                                        <div class="form-check option-item" data-tipe="<?= $opt['tipe_ternak'] ?? '' ?>">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   name="q<?= $q['questions_id'] ?>[]" 
                                                   value="<?= $opt['option_text'] ?>"
                                                   data-field="<?= $q['field_name'] ?>"
                                                   data-tipe="<?= $opt['tipe_ternak'] ?? '' ?>"
                                                   id="checkbox_<?= $q['questions_id'] ?>_<?= $opt['options_id'] ?? rand() ?>">
                                            <label class="form-check-label" for="checkbox_<?= $q['questions_id'] ?>_<?= $opt['options_id'] ?? rand() ?>">
                                                <?= $opt['option_text'] ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                <?php else: ?>
                    <div class="alert alert-info">
                        <p class="mb-0 fst-italic">Tidak ada pertanyaan.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="loading" id="loading">Memuat pertanyaan...</div>
            
            <button type="submit" class="btn btn-primary px-4 py-2 mt-4" id="submitBtn">Submit</button>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // AUTO-RESIZE TEXTAREA FUNCTIONS
        function autoResizeTextarea(textarea) {
            textarea.style.height = 'auto';
            
            const computed = window.getComputedStyle(textarea);
            const minHeight = parseInt(computed.minHeight);
            const padding = parseInt(computed.paddingTop) + parseInt(computed.paddingBottom);
            const border = parseInt(computed.borderTopWidth) + parseInt(computed.borderBottomWidth);
            
            const newHeight = Math.max(minHeight, textarea.scrollHeight + border);
            textarea.style.height = newHeight + 'px';
        }

        function initializeAutoResize() {
            const textareas = document.querySelectorAll('.auto-resize-textarea');
            
            textareas.forEach(textarea => {
                autoResizeTextarea(textarea);
                
                textarea.addEventListener('input', function() {
                    autoResizeTextarea(this);
                });
                
                textarea.addEventListener('paste', function() {
                    setTimeout(() => autoResizeTextarea(this), 10);
                });
                
                textarea.addEventListener('focus', function() {
                    autoResizeTextarea(this);
                });
            });
        }

        // INPUT VALIDATION FUNCTIONS
        function isLetterKey(evt) {
            const charCode = (evt.which) ? evt.which : evt.keyCode;
            
            if (charCode === 46 || charCode === 8 || charCode === 9 || 
                charCode === 27 || charCode === 13 || charCode === 32 ||
                (charCode >= 37 && charCode <= 40)) {
                return true;
            }
            
            if ((charCode < 65 || charCode > 90) && (charCode < 97 || charCode > 122)) {
                evt.preventDefault();
                return false;
            }
            
            return true;
        }

        function filterLettersOnly(input) {
            let value = input.value.replace(/[^a-zA-Z\s]/g, '');
            value = value.replace(/\s+/g, ' ').trim();
            input.value = value;
        }

        function handleLettersOnlyPaste(evt) {
            evt.preventDefault();
            let paste = (evt.clipboardData || window.clipboardData).getData('text');
            let cleanValue = paste.replace(/[^a-zA-Z\s]/g, '');
            cleanValue = cleanValue.replace(/\s+/g, ' ').trim();
            evt.target.value = cleanValue;
        }

        function isIntegerKey(evt) {
            const charCode = (evt.which) ? evt.which : evt.keyCode;
            
            if (charCode === 46 || charCode === 8 || charCode === 9 || 
                charCode === 27 || charCode === 13 || 
                (charCode >= 37 && charCode <= 40)) {
                return true;
            }
            
            if (charCode < 48 || charCode > 57) {
                evt.preventDefault();
                return false;
            }
            
            return true;
        }

        function formatIntegerWithComma(input) {
            let value = input.value.replace(/[^\d]/g, '');
            
            if (value === '') {
                input.value = '';
                return;
            }
            
            let formatted = parseInt(value, 10).toLocaleString('en-US');
            input.value = formatted;
        }

        function handleIntegerPaste(evt) {
            evt.preventDefault();
            let paste = (evt.clipboardData || window.clipboardData).getData('text');
            let cleanValue = paste.replace(/[^\d]/g, '');
            
            if (cleanValue !== '') {
                let formatted = parseInt(cleanValue, 10).toLocaleString('en-US');
                evt.target.value = formatted;
            }
        }

        function formatCurrencyWithComma(input) {
            let value = input.value.replace(/[^\d]/g, '');
            
            if (value === '') {
                input.value = '';
                return;
            }
            
            let formatted = parseInt(value).toLocaleString('en-US');
            input.value = formatted;
        }

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

        // FARM DROPDOWN FUNCTIONS
        function toggleFarmDropdown(questionId) {
            const dropdown = document.getElementById(`namaFarmDropdown_${questionId}`);
            const button = document.getElementById(`namaFarmToggle_${questionId}`);
            
            if (button.disabled || button.classList.contains('disabled')) {
                return;
            }
            
            // Close other dropdowns
            const allDropdowns = document.querySelectorAll('[id^="namaFarmDropdown_"]');
            allDropdowns.forEach(dd => {
                if (dd.id !== `namaFarmDropdown_${questionId}`) {
                    dd.classList.remove('show');
                }
            });
            
            dropdown.classList.toggle('show');
            
            if (dropdown.classList.contains('show')) {
                setTimeout(() => {
                    document.getElementById(`farmSearchInput_${questionId}`).focus();
                }, 100);
            }
        }

        function closeFarmDropdown(questionId) {
            if (questionId) {
                document.getElementById(`namaFarmDropdown_${questionId}`).classList.remove('show');
            } else {
                const allDropdowns = document.querySelectorAll('[id^="namaFarmDropdown_"]');
                allDropdowns.forEach(dd => dd.classList.remove('show'));
            }
        }

        function selectFarmOption(questionId, farmName) {
            document.getElementById(`namaFarmHidden_${questionId}`).value = farmName;
            document.getElementById(`selectedFarmText_${questionId}`).textContent = farmName;
            document.getElementById(`namaFarmToggle_${questionId}`).classList.add('selected-farm');
            
            closeFarmDropdown(questionId);
            
            document.getElementById(`farmSearchInput_${questionId}`).value = '';
            
            const allFarmOptions = document.querySelectorAll(`#namaFarmDropdown_${questionId} .farm-option`);
            allFarmOptions.forEach(function(option) {
                if (!option.hasAttribute('data-hidden-by-tipe')) {
                    option.style.display = 'block';
                }
            });
        }

        function filterFarmOptions(questionId) {
            const input = document.getElementById(`farmSearchInput_${questionId}`);
            const filter = input.value.toUpperCase();
            const options = document.querySelectorAll(`#namaFarmDropdown_${questionId} .farm-option`);
            
            options.forEach(function(option) {
                const txtValue = option.textContent || option.innerText;
                
                if (txtValue.toUpperCase().indexOf(filter) > -1 && 
                    !option.hasAttribute('data-hidden-by-tipe')) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            });
        }

        // MAIN LOGIC FUNCTIONS - MODIFIED FOR STRAIN AND PAKAN ENABLING
        function disableDependentElements() {
            // Disable nama peternak
            const namaPeternakSelects = document.querySelectorAll('select[data-field="nama_peternak"]');
            namaPeternakSelects.forEach(select => {
                select.disabled = true;
                select.value = '';
            });

            // Disable farm dropdown
            const namaFarmButtons = document.querySelectorAll('[id^="namaFarmToggle_"]');
            namaFarmButtons.forEach(button => {
                button.disabled = true;
                button.classList.add('disabled');
                
                const questionId = button.id.split('_')[1];
                const selectedText = document.getElementById(`selectedFarmText_${questionId}`);
                const hiddenInput = document.getElementById(`namaFarmHidden_${questionId}`);
                
                if (selectedText) selectedText.textContent = '-- Pilih Nama Farm --';
                if (hiddenInput) hiddenInput.value = '';
                button.classList.remove('selected-farm');
            });

            // Disable strain and pakan elements
            disableStrainAndPakanElements();
        }

        function enableDependentElements(selectedTipe) {
            // Enable nama peternak
            const namaPeternakSelects = document.querySelectorAll('select[data-field="nama_peternak"]');
            namaPeternakSelects.forEach(select => {
                select.disabled = false;
            });

            // Enable farm dropdown
            const namaFarmButtons = document.querySelectorAll('[id^="namaFarmToggle_"]');
            namaFarmButtons.forEach(button => {
                button.disabled = false;
                button.classList.remove('disabled');
            });

            // IMMEDIATELY enable strain and pakan elements after tipe ternak selection
            enableStrainAndPakanElements();

            // Filter options by tipe
            filterOptionsByTipe(selectedTipe);
        }

        function disableStrainAndPakanElements() {
            // Disable all strain and pakan elements
            const strainAndPakanQuestions = document.querySelectorAll('[data-field*="strain"], [data-field*="pakan"]');
            strainAndPakanQuestions.forEach(question => {
                const inputs = question.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    input.disabled = true;
                    if (input.type === 'radio' || input.type === 'checkbox') {
                        input.checked = false;
                    } else {
                        input.value = '';
                    }
                });
            });
        }

        function enableStrainAndPakanElements() {
            // Enable strain and pakan elements
            const strainAndPakanQuestions = document.querySelectorAll('[data-field*="strain"], [data-field*="pakan"]');
            strainAndPakanQuestions.forEach(question => {
                const inputs = question.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    input.disabled = false;
                });
                
                // Re-initialize auto-resize for any textareas
                const textareas = question.querySelectorAll('.auto-resize-textarea');
                textareas.forEach(textarea => {
                    autoResizeTextarea(textarea);
                });
            });
        }

        function hideAllOptionsByTipe() {
            // Hide all options EXCEPT for jenis ternak pedaging options
            const allOptions = document.querySelectorAll('.option-item');
            allOptions.forEach(function(option) {
                // Check if this option belongs to jenis ternak pedaging question
                const questionGroup = option.closest('.question-group');
                if (questionGroup) {
                    const label = questionGroup.querySelector('label');
                    // Keep jenis ternak pedaging options always visible
                    if (label && label.textContent.toLowerCase().includes('jenis ternak pedaging')) {
                        return; // Don't hide these options
                    }
                }
                option.style.display = 'none';
            });
        }
        
        function resetDependentSelects() {
            // Reset nama peternak select
            const namaPeternakSelects = document.querySelectorAll('select[data-field="nama_peternak"]');
            namaPeternakSelects.forEach(select => {
                select.selectedIndex = 0;
            });

            // Reset strain selects
            const strainSelects = document.querySelectorAll('select[data-field*="strain"]');
            strainSelects.forEach(select => {
                select.selectedIndex = 0;
            });

            // Reset pakan selects
            const pakanSelects = document.querySelectorAll('select[data-field*="pakan"]');
            pakanSelects.forEach(select => {
                select.selectedIndex = 0;
            });

            // Reset farm dropdowns
            const namaFarmButtons = document.querySelectorAll('[id^="namaFarmToggle_"]');
            namaFarmButtons.forEach(button => {
                const questionId = button.id.split('_')[1];
                const selectedText = document.getElementById(`selectedFarmText_${questionId}`);
                const hiddenInput = document.getElementById(`namaFarmHidden_${questionId}`);
                
                if (selectedText) selectedText.textContent = '-- Pilih Nama Farm --';
                if (hiddenInput) hiddenInput.value = '';
                button.classList.remove('selected-farm');
                closeFarmDropdown(questionId);
            });

            // DO NOT RESET the jenis ternak dropdown - keep it selected
        }
        
        function filterOptionsByTipe(selectedTipe) {
            if (!selectedTipe) {
                hideAllOptionsByTipe();
                return;
            }
            
            // Show/hide regular options based on tipe ternak
            const allOptions = document.querySelectorAll('.option-item:not(.farm-option)');
            allOptions.forEach(function(option) {
                const optionTipe = option.getAttribute('data-tipe') || option.dataset.tipe;
                
                if (!optionTipe || optionTipe === '' || optionTipe === selectedTipe || 
                    optionTipe.toLowerCase() === selectedTipe.toLowerCase()) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            });

            // Handle farm dropdown options
            const farmOptions = document.querySelectorAll('.farm-option');
            farmOptions.forEach(function(option) {
                const optionTipe = option.getAttribute('data-tipe') || option.dataset.tipe;
                
                if (!optionTipe || optionTipe === '' || optionTipe === selectedTipe || 
                    optionTipe.toLowerCase() === selectedTipe.toLowerCase()) {
                    option.style.display = 'block';
                    option.removeAttribute('data-hidden-by-tipe');
                } else {
                    option.style.display = 'none';
                    option.setAttribute('data-hidden-by-tipe', 'true');
                }
            });
            
            // Reset selects that have filtered options
            const allSelects = document.querySelectorAll('select:not([data-field*="jenis_ternak"])');
            allSelects.forEach(function(select) {
                select.selectedIndex = 0;
                
                const selectOptions = select.querySelectorAll('option.option-item');
                selectOptions.forEach(function(option) {
                    const optionTipe = option.getAttribute('data-tipe') || option.dataset.tipe;
                    
                    if (!optionTipe || optionTipe === '' || optionTipe === selectedTipe || 
                        optionTipe.toLowerCase() === selectedTipe.toLowerCase()) {
                        option.style.display = 'block';
                        option.disabled = false;
                    } else {
                        option.style.display = 'none';
                        option.disabled = true;
                    }
                });
            });
        }

        function changeTipeTermak(tipeTermak) {
            document.getElementById('selected_tipe_ternak').value = tipeTermak;
            
            if (tipeTermak === '') {
                disableDependentElements();
                hideAllOptionsByTipe();
                return;
            }
            
            // Reset dependent selects FIRST (but not jenis ternak itself)
            resetDependentSelects();
            
            // Then enable dependent elements (strain and pakan will be enabled here)
            enableDependentElements(tipeTermak);

            // Show loading and get updated questions via AJAX
            document.getElementById('loading').style.display = 'block';
            document.getElementById('submitBtn').disabled = true;
            
            // Make AJAX call to get filtered options
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '<?= base_url('Visiting_Pedaging_Controller/get_options_by_livestock_type') ?>', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        // Handle the response if needed
                        document.getElementById('loading').style.display = 'none';
                        document.getElementById('submitBtn').disabled = false;
                        
                        // Ensure the jenis ternak dropdown keeps its selected value
                        const jenisTeternakSelect = document.querySelector('select[onchange*="changeTipeTermak"]');
                        if (jenisTeternakSelect && jenisTeternakSelect.value !== tipeTermak) {
                            jenisTeternakSelect.value = tipeTermak;
                        }
                    } catch (e) {
                        console.error('Error processing response:', e);
                        document.getElementById('loading').style.display = 'none';
                        document.getElementById('submitBtn').disabled = false;
                    }
                }
            };
            
            xhr.send('livestock_type=' + encodeURIComponent(tipeTermak));
        }

        // Number formatting functions for specific fields
        function formatNumber(input) {
            let value = input.value.replace(/\D/g, '');
            
            if (value !== '') {
                value = parseInt(value).toLocaleString('en-US');
                input.value = value;
            }
        }

        function handleBlur(input) {
            if (input.value !== '') {
                let value = input.value.replace(/[^\d,]/g, '');
                value = value.replace(/,/g, '');
                value = parseInt(value).toLocaleString('en-US');
                input.value = value;
            }
        }

        // INITIALIZATION
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize auto-resize for textareas
            initializeAutoResize();
            
            // Initially disable all dependent elements
            disableDependentElements();
            hideAllOptionsByTipe();
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!event.target.closest('.custom-dropdown')) {
                    closeFarmDropdown();
                }
            });

            // Form submit handler for number formatting
            document.getElementById('pulletForm').addEventListener('submit', function(e) {
                const numberInputs = document.querySelectorAll('.number-format, .integer-field');
                numberInputs.forEach(function(input) {
                    input.value = input.value.replace(/,/g, '');
                });
            });
        });

        // Re-initialize auto-resize when window is resized
        window.addEventListener('resize', function() {
            const textareas = document.querySelectorAll('.auto-resize-textarea');
            textareas.forEach(textarea => {
                autoResizeTextarea(textarea);
            });
        });
    </script>
</body>
</html>
