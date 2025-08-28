<!DOCTYPE html>
<html>
<head>
    <title>Pedaging</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-container { margin-left: 20px; }
        .page-title { margin-left: 10px; }
        .auto-resize-textarea { resize: none; min-height: 80px; overflow: hidden; line-height: 1.4; }
        
        .custom-dropdown { position: relative; }
        .dropdown-toggle {
            background-color: white; color: #333; border: 1px solid #dee2e6; cursor: pointer;
            text-align: left; display: flex; justify-content: space-between; align-items: center;
        }
        .dropdown-toggle:hover, .dropdown-toggle:focus { background-color: #f8f9fa; border-color: #0d6efd; }
        .dropdown-toggle:disabled, .dropdown-toggle.disabled { background-color: #e9ecef; color: #6c757d; cursor: not-allowed; }
        .dropdown-toggle::after { content: "▼"; font-size: 12px; }
        
        .farm-search-input { border: none; border-bottom: 1px solid #dee2e6; background-color: #f8f9fa; }
        .farm-search-input:focus { outline: 2px solid #0d6efd; background-color: white; }
        
        .dropdown-content {
            display: none; position: absolute; background-color: white; min-width: 100%; max-height: 200px;
            overflow-y: auto; border: 1px solid #dee2e6; border-radius: 0 0 0.375rem 0.375rem;
            border-top: none; z-index: 1000; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
        }
        .dropdown-content .farm-option {
            color: #333; padding: 10px 12px; text-decoration: none; display: block;
            cursor: pointer; border-bottom: 1px solid #eee;
        }
        .dropdown-content .farm-option:hover { background-color: #f8f9fa; }
        .dropdown-content .farm-option:last-child { border-bottom: none; }
        .show { display: block; }
        .selected-farm { background-color: #fefefeff; border-color: #dee2e6; }

        .integer-input, .currency-input, .varchar-input, .letters-only-input { position: relative; max-width: 400px; }
        .currency-prefix { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #666; pointer-events: none; }
        .currency-input input { padding-left: 30px; }
        .loading { display: none; color: #666; font-style: italic; }
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

                    // Function to render question
                    function renderQuestion($q, $isJenisTernak = false) {
                        $required = !empty($q['required']) ? 'required' : '';
                        $requiredAsterisk = !empty($q['required']) ? '<span class="text-danger">*</span>' : '';
                        $inputName = 'q' . $q['questions_id'];
                        
                        $html = '<div class="mb-4 question-group" data-field="' . $q['field_name'] . '">';
                        $html .= '<label class="form-label fw-bold">' . $q['question_text'] . ' ' . $requiredAsterisk . '</label>';
                        
                        if ($q['type'] === 'select' && !empty($q['options'])) {
                            if ($q['field_name'] === 'nama_farm') {
                                // Custom farm dropdown
                                $html .= '<div class="custom-dropdown" style="max-width: 400px;">';
                                $html .= '<input type="hidden" name="' . $inputName . '" id="namaFarmHidden_' . $q['questions_id'] . '" data-field="' . $q['field_name'] . '" ' . $required . '>';
                                $html .= '<button type="button" onclick="toggleFarmDropdown(\'' . $q['questions_id'] . '\')" class="btn dropdown-toggle w-100 disabled" id="namaFarmToggle_' . $q['questions_id'] . '" disabled>';
                                $html .= '<span id="selectedFarmText_' . $q['questions_id'] . '">-- Pilih Nama Farm --</span></button>';
                                $html .= '<div id="namaFarmDropdown_' . $q['questions_id'] . '" class="dropdown-content w-100">';
                                $html .= '<input type="text" placeholder="Cari nama farm..." id="farmSearchInput_' . $q['questions_id'] . '" class="form-control farm-search-input" onkeyup="filterFarmOptions(\'' . $q['questions_id'] . '\')">';
                                
                                foreach ($q['options'] as $opt) {
                                    $html .= '<div class="farm-option option-item" data-value="' . $opt['option_text'] . '" data-tipe="' . ($opt['tipe_ternak'] ?? '') . '" onclick="selectFarmOption(\'' . $q['questions_id'] . '\', \'' . $opt['option_text'] . '\')">' . $opt['option_text'] . '</div>';
                                }
                                $html .= '</div></div>';
                            } else {
                                // Regular select
                                $onchange = $isJenisTernak ? 'onchange="changeTipeTermak(this.value)"' : '';
                                $disabled = $q['field_name'] === 'nama_peternak' ? 'disabled' : '';
                                
                                $html .= '<select name="' . $inputName . '" data-field="' . $q['field_name'] . '" class="form-select" style="max-width: 400px;" ' . $onchange . ' ' . $disabled . ' ' . $required . '>';
                                $html .= '<option value="">-- Pilih Jawaban --</option>';
                                
                                foreach ($q['options'] as $opt) {
                                    $html .= '<option value="' . $opt['option_text'] . '" data-tipe="' . ($opt['tipe_ternak'] ?? '') . '" class="option-item">' . $opt['option_text'] . '</option>';
                                }
                                $html .= '</select>';
                            }
                        } elseif ($q['type'] === 'radio' && !empty($q['options'])) {
                            $html .= '<div class="mt-2">';
                            foreach ($q['options'] as $opt) {
                                $optId = 'radio_' . $q['questions_id'] . '_' . ($opt['options_id'] ?? rand());
                                $html .= '<div class="form-check option-item" data-tipe="' . ($opt['tipe_ternak'] ?? '') . '">';
                                $html .= '<input class="form-check-input" type="radio" name="' . $inputName . '" value="' . $opt['option_text'] . '" data-field="' . $q['field_name'] . '" data-tipe="' . ($opt['tipe_ternak'] ?? '') . '" id="' . $optId . '" ' . $required . '>';
                                $html .= '<label class="form-check-label" for="' . $optId . '">' . $opt['option_text'] . '</label>';
                                $html .= '</div>';
                            }
                            $html .= '</div>';
                        } elseif ($q['type'] === 'textarea') {
                            $html .= '<textarea name="' . $inputName . '" class="form-control auto-resize-textarea" data-field="' . $q['field_name'] . '" placeholder="Masukkan jawaban Anda" style="max-width: 400px;" ' . $required . '></textarea>';
                        } elseif ($q['type'] === 'text') {
                            $inputClass = 'form-control';
                            $placeholder = 'Masukkan jawaban Anda';
                            $extraAttrs = '';
                            $wrapper = '';
                            $wrapperEnd = '';
                            
                            if (isset($q['input_type'])) {
                                switch ($q['input_type']) {
                                    case 'integer':
                                        $wrapper = '<div class="integer-input">';
                                        $wrapperEnd = '</div>';
                                        $inputClass .= ' integer-field';
                                        $placeholder = 'Masukkan angka bulat';
                                        $extraAttrs = 'onkeypress="return isIntegerKey(event)" oninput="formatIntegerWithComma(this)" onpaste="handleIntegerPaste(event)"';
                                        break;
                                    case 'currency':
                                        $wrapper = '<div class="currency-input"><span class="currency-prefix">Rp</span>';
                                        $wrapperEnd = '</div>';
                                        $placeholder = ' 0';
                                        $extraAttrs = 'oninput="formatCurrencyWithComma(this)" onkeypress="return isNumberKey(event)"';
                                        break;
                                    case 'letters_only':
                                        $wrapper = '<div class="letters-only-input">';
                                        $wrapperEnd = '</div>';
                                        $inputClass .= ' letters-only-field';
                                        $extraAttrs = 'onkeypress="return isLetterKey(event)" oninput="filterLettersOnly(this)" onpaste="handleLettersOnlyPaste(event)"';
                                        break;
                                    case 'varchar':
                                        $wrapper = '<div class="varchar-input">';
                                        $wrapperEnd = '</div>';
                                        $inputClass .= ' varchar-field';
                                        break;
                                }
                            }
                            
                            // Special field handling
                            if ($q['field_name'] === 'efektif_terisi_pedaging') {
                                $inputClass .= ' number-format';
                                $extraAttrs = 'pattern="[0-9,]*" inputmode="numeric" onkeypress="return (event.charCode >= 48 && event.charCode <= 57)" oninput="formatNumber(this)" onblur="handleBlur(this)"';
                                $placeholder = 'Masukkan angka bulat';
                            } elseif ($q['field_name'] === 'umur_pedaging') {
                                $inputClass = 'form-control';
                                $extraAttrs = 'type="number" step="1" min="0" onkeydown="return event.keyCode === 8 || event.keyCode === 46 ? true : !isNaN(Number(event.key)) && event.key !== \'e\' && event.key !== \'.\'"';
                                $placeholder = 'Masukkan angka bulat';
                            } elseif (in_array($q['field_name'], ['deplesi_pedaging', 'intake_pedaging', 'pencapaian_berat_pedaging', 'keseragaman_pedaging', 'fcr_pedaging'])) {
                                $inputClass = 'form-control';
                                $extraAttrs = 'type="number" step="0.01" min="0"';
                                $placeholder = 'Masukkan angka';
                            }
                            
                            $html .= $wrapper;
                            $html .= '<input ' . ($extraAttrs ?: 'type="text"') . ' name="' . $inputName . '" data-field="' . $q['field_name'] . '" class="' . $inputClass . '" placeholder="' . $placeholder . '" style="max-width: 400px;" ' . $required . '>';
                            $html .= $wrapperEnd;
                        } elseif ($q['type'] === 'number') {
                            if (isset($q['input_type']) && $q['input_type'] === 'integer') {
                                $html .= '<div class="integer-input">';
                                $html .= '<input type="text" name="' . $inputName . '" data-field="' . $q['field_name'] . '" class="form-control integer-field" onkeypress="return isIntegerKey(event)" oninput="formatIntegerWithComma(this)" onpaste="handleIntegerPaste(event)" placeholder="Masukkan angka bulat" style="max-width: 400px;" ' . $required . '>';
                                $html .= '</div>';
                            } else {
                                $step = isset($q['step']) ? $q['step'] : '0.01';
                                $html .= '<input type="number" name="' . $inputName . '" data-field="' . $q['field_name'] . '" class="form-control" step="' . $step . '" min="0" placeholder="Masukkan angka" style="max-width: 400px;" ' . $required . '>';
                            }
                        } elseif ($q['type'] === 'date') {
                            $html .= '<input type="date" name="' . $inputName . '" data-field="' . $q['field_name'] . '" class="form-control" style="max-width: 400px;" ' . $required . '>';
                        } elseif ($q['type'] === 'checkbox' && !empty($q['options'])) {
                            $html .= '<div class="mt-2">';
                            foreach ($q['options'] as $opt) {
                                $optId = 'checkbox_' . $q['questions_id'] . '_' . ($opt['options_id'] ?? rand());
                                $html .= '<div class="form-check option-item" data-tipe="' . ($opt['tipe_ternak'] ?? '') . '">';
                                $html .= '<input class="form-check-input" type="checkbox" name="' . $inputName . '[]" value="' . $opt['option_text'] . '" data-field="' . $q['field_name'] . '" data-tipe="' . ($opt['tipe_ternak'] ?? '') . '" id="' . $optId . '">';
                                $html .= '<label class="form-check-label" for="' . $optId . '">' . $opt['option_text'] . '</label>';
                                $html .= '</div>';
                            }
                            $html .= '</div>';
                        }
                        
                        $html .= '</div>';
                        return $html;
                    }
                    ?>

                    <?php if ($jenis_ternak_q): ?>
                        <?php echo renderQuestion($jenis_ternak_q, true); ?>
                    <?php endif; ?>

                    <?php foreach ($other_questions as $q): ?>
                        <?php echo renderQuestion($q, false); ?>
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
        // Utility Functions
        const autoResizeTextarea = (textarea) => {
            textarea.style.height = 'auto';
            const computed = window.getComputedStyle(textarea);
            const minHeight = parseInt(computed.minHeight);
            const padding = parseInt(computed.paddingTop) + parseInt(computed.paddingBottom);
            const border = parseInt(computed.borderTopWidth) + parseInt(computed.borderBottomWidth);
            textarea.style.height = Math.max(minHeight, textarea.scrollHeight + border) + 'px';
        };

        const initializeAutoResize = () => {
            document.querySelectorAll('.auto-resize-textarea').forEach(textarea => {
                autoResizeTextarea(textarea);
                ['input', 'paste', 'focus'].forEach(event => {
                    textarea.addEventListener(event, () => setTimeout(() => autoResizeTextarea(textarea), 10));
                });
            });
        };

        // Input Validation
        const isLetterKey = (evt) => {
            const charCode = evt.which || evt.keyCode;
            if ([46, 8, 9, 27, 13, 32].includes(charCode) || (charCode >= 37 && charCode <= 40)) return true;
            if ((charCode < 65 || charCode > 90) && (charCode < 97 || charCode > 122)) {
                evt.preventDefault();
                return false;
            }
            return true;
        };

        const filterLettersOnly = (input) => {
            input.value = input.value.replace(/[^a-zA-Z\s]/g, '').replace(/\s+/g, ' ').trim();
        };

        const isIntegerKey = (evt) => {
            const charCode = evt.which || evt.keyCode;
            if ([46, 8, 9, 27, 13].includes(charCode) || (charCode >= 37 && charCode <= 40)) return true;
            if (charCode < 48 || charCode > 57) {
                evt.preventDefault();
                return false;
            }
            return true;
        };

        const formatIntegerWithComma = (input) => {
            const value = input.value.replace(/[^\d]/g, '');
            input.value = value === '' ? '' : parseInt(value, 10).toLocaleString('en-US');
        };

        const formatCurrencyWithComma = (input) => {
            const value = input.value.replace(/[^\d]/g, '');
            input.value = value === '' ? '' : parseInt(value).toLocaleString('en-US');
        };

        const isNumberKey = (evt) => {
            const charCode = evt.which || evt.keyCode;
            return [46, 8, 9, 27, 13].includes(charCode) || (charCode >= 48 && charCode <= 57);
        };

        // Farm Dropdown Functions
        const toggleFarmDropdown = (questionId) => {
            const dropdown = document.getElementById(`namaFarmDropdown_${questionId}`);
            const button = document.getElementById(`namaFarmToggle_${questionId}`);
            
            if (button.disabled || button.classList.contains('disabled')) return;
            
            document.querySelectorAll('[id^="namaFarmDropdown_"]').forEach(dd => {
                if (dd.id !== `namaFarmDropdown_${questionId}`) dd.classList.remove('show');
            });
            
            dropdown.classList.toggle('show');
            if (dropdown.classList.contains('show')) {
                setTimeout(() => document.getElementById(`farmSearchInput_${questionId}`).focus(), 100);
            }
        };

        const selectFarmOption = (questionId, farmName) => {
            document.getElementById(`namaFarmHidden_${questionId}`).value = farmName;
            document.getElementById(`selectedFarmText_${questionId}`).textContent = farmName;
            document.getElementById(`namaFarmToggle_${questionId}`).classList.add('selected-farm');
            closeFarmDropdown(questionId);
            document.getElementById(`farmSearchInput_${questionId}`).value = '';
            document.querySelectorAll(`#namaFarmDropdown_${questionId} .farm-option`).forEach(option => {
                if (!option.hasAttribute('data-hidden-by-tipe')) option.style.display = 'block';
            });
        };

        const filterFarmOptions = (questionId) => {
            const filter = document.getElementById(`farmSearchInput_${questionId}`).value.toUpperCase();
            document.querySelectorAll(`#namaFarmDropdown_${questionId} .farm-option`).forEach(option => {
                const txtValue = option.textContent || option.innerText;
                option.style.display = (txtValue.toUpperCase().indexOf(filter) > -1 && 
                    !option.hasAttribute('data-hidden-by-tipe')) ? 'block' : 'none';
            });
        };

        const closeFarmDropdown = (questionId) => {
            if (questionId) {
                document.getElementById(`namaFarmDropdown_${questionId}`).classList.remove('show');
            } else {
                document.querySelectorAll('[id^="namaFarmDropdown_"]').forEach(dd => dd.classList.remove('show'));
            }
        };

        // Main Logic Functions
        const toggleElements = (enable, selectedTipe = null) => {
            // Toggle nama peternak
            document.querySelectorAll('select[data-field="nama_peternak"]').forEach(select => {
                select.disabled = !enable;
                if (!enable) select.value = '';
            });

            // Toggle farm dropdown
            document.querySelectorAll('[id^="namaFarmToggle_"]').forEach(button => {
                button.disabled = !enable;
                button.classList.toggle('disabled', !enable);
                
                if (!enable) {
                    const questionId = button.id.split('_')[1];
                    const selectedText = document.getElementById(`selectedFarmText_${questionId}`);
                    const hiddenInput = document.getElementById(`namaFarmHidden_${questionId}`);
                    
                    if (selectedText) selectedText.textContent = '-- Pilih Nama Farm --';
                    if (hiddenInput) hiddenInput.value = '';
                    button.classList.remove('selected-farm');
                }
            });

            // Toggle strain and pakan
            document.querySelectorAll('[data-field*="strain"], [data-field*="pakan"]').forEach(question => {
                question.querySelectorAll('input, select, textarea').forEach(input => {
                    input.disabled = !enable;
                    if (!enable) {
                        if (['radio', 'checkbox'].includes(input.type)) {
                            input.checked = false;
                        } else {
                            input.value = '';
                        }
                    }
                });
            });

            if (enable && selectedTipe) filterOptionsByTipe(selectedTipe);
        };

        const filterOptionsByTipe = (selectedTipe) => {
            if (!selectedTipe) {
                document.querySelectorAll('.option-item').forEach(option => {
                    const questionGroup = option.closest('.question-group');
                    if (questionGroup) {
                        const label = questionGroup.querySelector('label');
                        if (!label || !label.textContent.toLowerCase().includes('jenis ternak pedaging')) {
                            option.style.display = 'none';
                        }
                    }
                });
                return;
            }

            // Show/hide options based on tipe ternak
            document.querySelectorAll('.option-item:not(.farm-option)').forEach(option => {
                const optionTipe = option.getAttribute('data-tipe') || option.dataset.tipe;
                option.style.display = (!optionTipe || optionTipe === selectedTipe) ? 'block' : 'none';
            });

            // Handle farm dropdown options
            document.querySelectorAll('.farm-option').forEach(option => {
                const optionTipe = option.getAttribute('data-tipe') || option.dataset.tipe;
                if (!optionTipe || optionTipe === selectedTipe) {
                    option.style.display = 'block';
                    option.removeAttribute('data-hidden-by-tipe');
                } else {
                    option.style.display = 'none';
                    option.setAttribute('data-hidden-by-tipe', 'true');
                }
            });

            // Reset selects
            document.querySelectorAll('select:not([data-field*="jenis_ternak"])').forEach(select => {
                select.selectedIndex = 0;
                select.querySelectorAll('option.option-item').forEach(option => {
                    const optionTipe = option.getAttribute('data-tipe') || option.dataset.tipe;
                    const shouldShow = !optionTipe || optionTipe === selectedTipe;
                    option.style.display = shouldShow ? 'block' : 'none';
                    option.disabled = !shouldShow;
                });
            });
        };

        const changeTipeTermak = (tipeTermak) => {
            document.getElementById('selected_tipe_ternak').value = tipeTermak;
            
            if (!tipeTermak) {
                toggleElements(false);
                return;
            }
            
            // Reset dependent selects but keep jenis ternak
            document.querySelectorAll('select[data-field="nama_peternak"], [data-field*="strain"] select, [data-field*="pakan"] select').forEach(select => {
                select.selectedIndex = 0;
            });

            document.querySelectorAll('[id^="namaFarmToggle_"]').forEach(button => {
                const questionId = button.id.split('_')[1];
                document.getElementById(`selectedFarmText_${questionId}`).textContent = '-- Pilih Nama Farm --';
                document.getElementById(`namaFarmHidden_${questionId}`).value = '';
                button.classList.remove('selected-farm');
                closeFarmDropdown(questionId);
            });

            toggleElements(true, tipeTermak);

            // AJAX call
            document.getElementById('loading').style.display = 'block';
            document.getElementById('submitBtn').disabled = true;
            
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '<?= base_url('Visiting_Pedaging_Controller/get_options_by_livestock_type') ?>', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onreadystatechange = () => {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('submitBtn').disabled = false;
                    
                    const jenisTeternakSelect = document.querySelector('select[onchange*="changeTipeTermak"]');
                    if (jenisTeternakSelect && jenisTeternakSelect.value !== tipeTermak) {
                        jenisTeternakSelect.value = tipeTermak;
                    }
                }
            };
            
            xhr.send('livestock_type=' + encodeURIComponent(tipeTermak));
        };

        // Number formatting
        const formatNumber = (input) => {
            const value = input.value.replace(/\D/g, '');
            input.value = value !== '' ? parseInt(value).toLocaleString('en-US') : '';
        };

        const handleBlur = (input) => {
            if (input.value !== '') {
                const value = input.value.replace(/[^\d,]/g, '').replace(/,/g, '');
                input.value = parseInt(value).toLocaleString('en-US');
            }
        };

        // Event handlers for paste events
        const handleIntegerPaste = (evt) => {
            evt.preventDefault();
            const paste = (evt.clipboardData || window.clipboardData).getData('text');
            const cleanValue = paste.replace(/[^\d]/g, '');
            if (cleanValue) evt.target.value = parseInt(cleanValue, 10).toLocaleString('en-US');
        };

        const handleLettersOnlyPaste = (evt) => {
            evt.preventDefault();
            const paste = (evt.clipboardData || window.clipboardData).getData('text');
            evt.target.value = paste.replace(/[^a-zA-Z\s]/g, '').replace(/\s+/g, ' ').trim();
        };

        // Initialization
        document.addEventListener('DOMContentLoaded', () => {
            initializeAutoResize();
            toggleElements(false);
            
            document.addEventListener('click', (event) => {
                if (!event.target.closest('.custom-dropdown')) closeFarmDropdown();
            });

            document.getElementById('pulletForm').addEventListener('submit', () => {
                document.querySelectorAll('.number-format, .integer-field').forEach(input => {
                    input.value = input.value.replace(/,/g, '');
                });
            });
        });

        window.addEventListener('resize', () => {
            document.querySelectorAll('.auto-resize-textarea').forEach(autoResizeTextarea);
        });
    </script>
</body>
</html>
