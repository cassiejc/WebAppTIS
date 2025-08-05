<!DOCTYPE html>
<html>
<head>
    <title>Form Visiting</title>
    <style>
        form { margin-left: 20px; }
        h2 { margin-left: 10px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input[type="text"], 
        .form-group input[type="date"], 
        .form-group textarea, 
        .form-group select { 
            width: 100%; 
            max-width: 400px; 
            padding: 8px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
        }
        .form-group textarea { 
            resize: vertical; 
            min-height: 80px; 
        }
        .radio-group, .checkbox-group { 
            margin-top: 5px; 
        }
        .radio-group label, .checkbox-group label { 
            font-weight: normal; 
            margin-left: 5px; 
        }
        .required { color: red; }
        .btn-submit { 
            background-color: #007bff; 
            color: white; 
            padding: 10px 20px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            margin-top: 20px; 
        }
        .btn-submit:hover { 
            background-color: #0056b3; 
        }
        .no-questions { 
            margin: 20px; 
            color: #666; 
            font-style: italic; 
        }
        .jenis-kasus-dropdown {
            display: none !important;
        }
        
        .jenis-kasus-dropdown.show {
            display: block !important;
        }
    </style>
    <script>
        // Global flag to prevent hiding dropdowns when user is interacting
        let isUserInteractingWithDropdown = false;
        
        // Function to hide jenis kasus dropdown fields
        function hideJenisKasusFields() {
            if (isUserInteractingWithDropdown) {
                return;
            }
            
            const allFormGroups = document.querySelectorAll('.form-group');
            allFormGroups.forEach(function(group) {
                const label = group.querySelector('label');
                if (label) {
                    const labelText = label.textContent.toLowerCase();
                    if ((labelText.includes('bacterial') || labelText.includes('virus') || 
                        labelText.includes('parasit') || labelText.includes('jamur') || 
                        labelText.includes('lain-lain') || labelText.includes('lain_lain')) && 
                        !labelText.includes('jenis kasus')) {
                        
                        // Check if this field should be visible based on current selection
                        const selectedJenisKasus = document.querySelector('input[type="radio"]:checked');
                        if (selectedJenisKasus) {
                            const selectedLabel = selectedJenisKasus.closest('.form-group').querySelector('label');
                            if (selectedLabel && selectedLabel.textContent.toLowerCase().includes('jenis kasus')) {
                                const fieldMapping = {
                                    'Bacterial': 'bacterial',
                                    'Viral': 'virus',
                                    'Parasit': 'parasit',
                                    'Jamur': 'jamur',
                                    'Lain-lain': 'lain_lain'
                                };
                                
                                const targetFieldName = fieldMapping[selectedJenisKasus.value];
                                if (targetFieldName && (labelText.includes(targetFieldName.toLowerCase()) || 
                                    (targetFieldName === 'virus' && labelText.includes('virus')) ||
                                    (targetFieldName === 'lain_lain' && (labelText.includes('lain-lain') || labelText.includes('lain_lain'))))) {
                                    return;
                                }
                            }
                        }
                        
                        // Check if this dropdown has a selected value - if so, don't hide it
                        const selectElement = group.querySelector('select');
                        if (selectElement && selectElement.value && selectElement.value !== '') {
                            return;
                        }
                        
                        // Hide using CSS class
                        group.classList.add('jenis-kasus-dropdown');
                        group.classList.remove('show');
                        
                        const inputs = group.querySelectorAll('input, select, textarea');
                        inputs.forEach(function(input) {
                            input.disabled = true;
                            input.removeAttribute('required');
                            if (input.type === 'radio' || input.type === 'checkbox') {
                                input.checked = false;
                            } else {
                                input.value = '';
                            }
                        });
                    }
                }
            });
        }
        
        // Function to check initial tujuan kunjungan state
        function checkInitialTujuanKunjungan() {
            const kasusRadio = document.querySelector('input[type="radio"][value="Kasus"]:checked');
            const monitoringRadio = document.querySelector('input[type="radio"][value="Monitoring"]:checked');
            
            const formGroups = document.querySelectorAll('.form-group');
            formGroups.forEach(function(group) {
                const groupLabel = group.querySelector('label');
                if (groupLabel && groupLabel.textContent.toLowerCase().includes('jenis kasus')) {
                    if (kasusRadio) {
                        group.style.display = 'block';
                        const inputs = group.querySelectorAll('input, select, textarea');
                        inputs.forEach(function(input) {
                            input.disabled = false;
                            if (groupLabel.innerHTML.includes('<span class="required">*</span>')) {
                                input.setAttribute('required', 'required');
                            }
                        });
                        
                        const selectedJenisKasus = group.querySelector('input[type="radio"]:checked');
                        if (selectedJenisKasus) {
                            toggleJenisKasusFields(selectedJenisKasus.value);
                        }
                    } else {
                        group.style.display = 'none';
                        const inputs = group.querySelectorAll('input, select, textarea');
                        inputs.forEach(function(input) {
                            input.disabled = true;
                            input.removeAttribute('required');
                            if (input.type === 'radio' || input.type === 'checkbox') {
                                input.checked = false;
                            } else {
                                input.value = '';
                            }
                        });
                    }
                }
            });
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            hideJenisKasusFields();
            checkInitialTujuanKunjungan();
            
            // Set up event listeners for dropdown interactions
            document.addEventListener('focus', function(e) {
                if (e.target.tagName === 'SELECT') {
                    const label = e.target.closest('.form-group').querySelector('label');
                    if (label) {
                        const labelText = label.textContent.toLowerCase();
                        if (labelText.includes('bacterial') || labelText.includes('virus') || 
                            labelText.includes('parasit') || labelText.includes('jamur') || 
                            labelText.includes('lain-lain') || labelText.includes('lain_lain')) {
                            
                            isUserInteractingWithDropdown = true;
                            e.target.disabled = false;
                            e.target.closest('.form-group').classList.remove('jenis-kasus-dropdown');
                            e.target.closest('.form-group').classList.add('show');
                        }
                    }
                }
            }, true);
            
            document.addEventListener('blur', function(e) {
                if (e.target.tagName === 'SELECT') {
                    const label = e.target.closest('.form-group').querySelector('label');
                    if (label) {
                        const labelText = label.textContent.toLowerCase();
                        if (labelText.includes('bacterial') || labelText.includes('virus') || 
                            labelText.includes('parasit') || labelText.includes('jamur') || 
                            labelText.includes('lain-lain') || labelText.includes('lain_lain')) {
                            
                            setTimeout(function() {
                                isUserInteractingWithDropdown = false;
                            }, 500);
                        }
                    }
                }
            }, true);
            
            // Hide fields when form changes
            document.addEventListener('change', function() {
                if (!isUserInteractingWithDropdown) {
                    setTimeout(hideJenisKasusFields, 10);
                }
            });
        });
    </script>
</head>
<body>
    <h2>Form Visiting - <?php echo $current_sub_area['nama_sub_area']; ?></h2>

    <form method="post" action="" id="visitingForm">
        <input type="hidden" name="action" value="next">
        <div id="initialQuestions">
            <?php if (!empty($questions)): ?>
                <?php foreach ($questions as $q): ?>
                    <div class="form-group">
                        <label>
                            <?= $q['question_text'] ?>
                            <?php if (!empty($q['required'])): ?> 
                                <span class="required">*</span> 
                            <?php endif; ?>
                        </label>
                        
                        <?php if ($q['type'] == 'radio' && !empty($q['options'])): ?>
                            <div class="radio-group">
                                <?php foreach ($q['options'] as $opt): ?>
                                    <label>
                                        <input type="radio" 
                                               name="q<?= $q['questions_id'] ?>" 
                                               value="<?= $opt['option_text'] ?>" 
                                               <?= !empty($q['required']) ? 'required' : '' ?>
                                               <?php if ($q['field_name'] == 'kunjungan_ke'): ?>onchange="loadSpecificQuestions('<?= $opt['option_text'] ?>')"<?php endif; ?>
                                               <?php if ($q['field_name'] == 'tujuan_kunjungan'): ?>onchange="toggleJenisKasus(this.value)"<?php endif; ?>
                                               <?php if ($q['field_name'] == 'jenis_kasus'): ?>onchange="toggleJenisKasusFields(this.value)"<?php endif; ?>
                                               > 
                                        <?= $opt['option_text'] ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php elseif ($q['type'] == 'text'): ?>
                            <input type="text" 
                                   name="q<?= $q['questions_id'] ?>" 
                                   placeholder="Masukkan jawaban Anda"
                                   <?= !empty($q['required']) ? 'required' : '' ?>>
                        <?php elseif ($q['type'] == 'textarea'): ?>
                            <textarea name="q<?= $q['questions_id'] ?>" 
                                      placeholder="Masukkan jawaban Anda"
                                      <?= !empty($q['required']) ? 'required' : '' ?>></textarea>
                        <?php elseif ($q['type'] == 'select' && !empty($q['options'])): ?>
                             <select name="q<?= $q['questions_id'] ?>" 
                                     <?= !empty($q['required']) ? 'required' : '' ?>
                                     <?php if ($q['field_name'] == 'kunjungan_ke'): ?>onchange="loadSpecificQuestions(this.value)"<?php endif; ?>>
                                 <option value="">-- Pilih Jawaban --</option>
                                 <?php foreach ($q['options'] as $opt): ?>
                                     <option value="<?= $opt['option_text'] ?>">
                                         <?= $opt['option_text'] ?>
                                     </option>
                                 <?php endforeach; ?>
                             </select>
                        <?php elseif ($q['type'] == 'date'): ?>
                            <input type="date" 
                                   name="q<?= $q['questions_id'] ?>" 
                                   <?= !empty($q['required']) ? 'required' : '' ?>>
                        <?php elseif ($q['type'] == 'checkbox' && !empty($q['options'])): ?>
                            <div class="checkbox-group">
                                <?php foreach ($q['options'] as $opt): ?>
                                    <label>
                                        <input type="checkbox" 
                                               name="q<?= $q['questions_id'] ?>[]" 
                                               value="<?= $opt['option_text'] ?>"> 
                                        <?= $opt['option_text'] ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-questions">Tidak ada pertanyaan untuk form visiting.</p>
            <?php endif; ?>
        </div>
        
        <div id="dynamicQuestions" style="display: none;">
            <h3 id="dynamicTitle"></h3>
            <div id="dynamicFormContent"></div>
        </div>
        
        <button type="submit" class="btn-submit" id="submitBtn" data-visiting-type="">Next</button>
    </form>

    <script>
        function loadSpecificQuestions(visitingType) {
            if (!visitingType || visitingType === '') {
                document.getElementById('dynamicQuestions').style.display = 'none';
                updateButtonText('');
                return;
            }
            
            updateButtonText(visitingType);
            
            document.getElementById('dynamicQuestions').style.display = 'block';
            document.getElementById('dynamicTitle').innerHTML = 'Memuat pertanyaan untuk ' + visitingType + '...';
            document.getElementById('dynamicFormContent').innerHTML = '<p>Loading...</p>';
            
            fetch('<?= base_url("Visiting_Controller/load_form_questions") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'visiting_type=' + encodeURIComponent(visitingType)
            })
            .then(response => response.json())
            .then(data => {
                if (data.questions && data.questions.length > 0) {
                    let formContent = '';
                    data.questions.forEach(function(q) {
                        formContent += '<div class="form-group">';
                        formContent += '<label>';
                        formContent += q.question_text;
                        if (q.required) {
                            formContent += ' <span class="required">*</span>';
                        }
                        formContent += '</label>';
                        
                        if (q.type === 'radio' && q.options && q.options.length > 0) {
                            formContent += '<div class="radio-group">';
                            q.options.forEach(function(opt) {
                                formContent += '<label>';
                                formContent += '<input type="radio" name="q' + q.questions_id + '" value="' + opt.option_text + '"';
                                if (q.required) {
                                    formContent += ' required';
                                }
                                if (q.field_name === 'tujuan_kunjungan') {
                                    formContent += ' onchange="toggleJenisKasus(this.value)"';
                                }
                                if (q.field_name === 'jenis_kasus') {
                                    formContent += ' onchange="toggleJenisKasusFields(this.value)"';
                                }
                                formContent += '> ' + opt.option_text;
                                formContent += '</label>';
                            });
                            formContent += '</div>';
                        } else if (q.type === 'text') {
                            formContent += '<input type="text" name="q' + q.questions_id + '" placeholder="Masukkan jawaban Anda"';
                            if (q.required) {
                                formContent += ' required';
                            }
                            formContent += '>';
                        } else if (q.type === 'textarea') {
                            formContent += '<textarea name="q' + q.questions_id + '" placeholder="Masukkan jawaban Anda"';
                            if (q.required) {
                                formContent += ' required';
                            }
                            formContent += '></textarea>';
                        } else if (q.type === 'select' && q.options && q.options.length > 0) {
                            formContent += '<select name="q' + q.questions_id + '"';
                            if (q.required) {
                                formContent += ' required';
                            }
                            formContent += '>';
                            formContent += '<option value="">-- Pilih Jawaban --</option>';
                            q.options.forEach(function(opt) {
                                formContent += '<option value="' + opt.option_text + '">' + opt.option_text + '</option>';
                            });
                            formContent += '</select>';
                        } else if (q.type === 'date') {
                            formContent += '<input type="date" name="q' + q.questions_id + '"';
                            if (q.required) {
                                formContent += ' required';
                            }
                            formContent += '>';
                        } else if (q.type === 'checkbox' && q.options && q.options.length > 0) {
                            formContent += '<div class="checkbox-group">';
                            q.options.forEach(function(opt) {
                                formContent += '<label>';
                                formContent += '<input type="checkbox" name="q' + q.questions_id + '[]" value="' + opt.option_text + '"> ' + opt.option_text;
                                formContent += '</label>';
                            });
                            formContent += '</div>';
                        }
                        formContent += '</div>';
                    });
                    
                    document.getElementById('dynamicTitle').innerHTML = 'Pertanyaan untuk ' + visitingType;
                    document.getElementById('dynamicFormContent').innerHTML = formContent;
                    
                    // Initialize event listeners for dynamic content
                    initializeDynamicEventListeners();
                    
                    // Hide dropdown fields after dynamic content is loaded
                    setTimeout(function() {
                        hideJenisKasusFields();
                        checkInitialTujuanKunjungan();
                    }, 50);
                } else {
                    document.getElementById('dynamicTitle').innerHTML = 'Tidak ada pertanyaan untuk ' + visitingType;
                    document.getElementById('dynamicFormContent').innerHTML = '<p class="no-questions">Tidak ada pertanyaan tambahan untuk jenis kunjungan ini.</p>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('dynamicTitle').innerHTML = 'Error';
                document.getElementById('dynamicFormContent').innerHTML = '<p class="no-questions">Terjadi kesalahan saat memuat pertanyaan.</p>';
            });
        }
        
        function updateButtonText(visitingType) {
            const submitBtn = document.getElementById('submitBtn');
            const actionInput = document.querySelector('input[name="action"]');
            
            if (visitingType === 'Peternak') {
                submitBtn.textContent = 'Next';
                actionInput.value = 'next';
            } else if (visitingType && visitingType !== '') {
                submitBtn.textContent = 'Submit';
                actionInput.value = 'submit';
            } else {
                submitBtn.textContent = 'Next';
                actionInput.value = 'next';
            }
        }
        
        function toggleJenisKasus(tujuanKunjungan) {
            const formGroups = document.querySelectorAll('.form-group');
            
            formGroups.forEach(function(group) {
                const label = group.querySelector('label');
                if (label) {
                    const labelText = label.textContent.toLowerCase();
                    if (labelText.includes('jenis kasus')) {
                        if (tujuanKunjungan === 'Monitoring') {
                            group.style.display = 'none';
                            clearAndDisableInputs(group);
                            hideAllJenisKasusFields();
                        } else if (tujuanKunjungan === 'Kasus') {
                            group.style.display = 'block';
                            restoreRequiredAttributes(group);
                        }
                    }
                }
            });
        }
        
        function toggleJenisKasusFields(jenisKasus) {
            const fieldMapping = {
                'Bacterial': 'bacterial',
                'Viral': 'virus',
                'Parasit': 'parasit',
                'Jamur': 'jamur',
                'Lain-lain': 'lain_lain',
                'Lambat puncak': null
            };
            
            const targetFieldName = fieldMapping[jenisKasus];
            
            if (targetFieldName === null) {
                hideAllJenisKasusFields();
                return;
            }
            
            if (!targetFieldName) return;
            
            const formGroups = document.querySelectorAll('.form-group');
            
            // Hide all jenis kasus dropdown fields first
            formGroups.forEach(function(group) {
                const label = group.querySelector('label');
                if (label) {
                    const labelText = label.textContent.toLowerCase();
                    if (labelText.includes('bacterial') || labelText.includes('virus') || 
                        labelText.includes('parasit') || labelText.includes('jamur') || 
                        labelText.includes('lain-lain') || labelText.includes('lain_lain')) {
                        
                        // Check if this dropdown has a selected value - if so, don't hide it
                        const selectElement = group.querySelector('select');
                        if (selectElement && selectElement.value && selectElement.value !== '') {
                            return;
                        }
                        
                        group.classList.add('jenis-kasus-dropdown');
                        group.classList.remove('show');
                        
                        const inputs = group.querySelectorAll('input, select, textarea');
                        inputs.forEach(function(input) {
                            input.disabled = true;
                            input.removeAttribute('required');
                            if (input.type === 'radio' || input.type === 'checkbox') {
                                input.checked = false;
                            } else {
                                input.value = '';
                            }
                        });
                    }
                }
            });
            
            // Show only the selected field
            formGroups.forEach(function(group) {
                const label = group.querySelector('label');
                if (label) {
                    const labelText = label.textContent.toLowerCase();
                    if (labelText.includes(targetFieldName.toLowerCase()) || 
                        (targetFieldName === 'virus' && labelText.includes('virus')) ||
                        (targetFieldName === 'lain_lain' && (labelText.includes('lain-lain') || labelText.includes('lain_lain')))) {
                        
                        group.classList.remove('jenis-kasus-dropdown');
                        group.classList.add('show');
                        
                        const inputs = group.querySelectorAll('input, select, textarea');
                        inputs.forEach(function(input) {
                            input.disabled = false;
                            input.removeAttribute('disabled');
                            if (label.innerHTML.includes('<span class="required">*</span>')) {
                                input.setAttribute('required', 'required');
                            }
                        });
                    }
                }
            });
        }
        
        function hideAllJenisKasusFields() {
            const formGroups = document.querySelectorAll('.form-group');
            
            formGroups.forEach(function(group) {
                const label = group.querySelector('label');
                if (label) {
                    const labelText = label.textContent.toLowerCase();
                    if ((labelText.includes('bacterial') || labelText.includes('virus') || 
                        labelText.includes('parasit') || labelText.includes('jamur') || 
                        labelText.includes('lain-lain') || labelText.includes('lain_lain')) && 
                        !labelText.includes('jenis kasus')) {
                        
                        group.classList.add('jenis-kasus-dropdown');
                        group.classList.remove('show');
                        
                        const inputs = group.querySelectorAll('input, select, textarea');
                        inputs.forEach(function(input) {
                            input.disabled = true;
                            input.removeAttribute('required');
                            if (input.type === 'radio' || input.type === 'checkbox') {
                                input.checked = false;
                            } else {
                                input.value = '';
                            }
                        });
                    }
                }
            });
        }
        
        function clearAndDisableInputs(group) {
            const inputs = group.querySelectorAll('input, select, textarea');
            inputs.forEach(function(input) {
                if (input.type === 'radio' || input.type === 'checkbox') {
                    input.checked = false;
                } else {
                    input.value = '';
                }
                input.removeAttribute('required');
                input.disabled = true;
                input.setAttribute('disabled', 'disabled');
            });
        }
        
        function restoreRequiredAttributes(group) {
            const inputs = group.querySelectorAll('input, select, textarea');
            const label = group.querySelector('label');
            const wasRequired = label && label.innerHTML.includes('<span class="required">*</span>');
            
            inputs.forEach(function(input) {
                input.disabled = false;
                input.removeAttribute('disabled');
                if (wasRequired) {
                    input.setAttribute('required', 'required');
                }
            });
        }
        
        function initializeDynamicEventListeners() {
            const dynamicContent = document.getElementById('dynamicFormContent');
            const radios = dynamicContent.querySelectorAll('input[type="radio"]');
            
            radios.forEach(function(radio) {
                const label = radio.closest('.form-group').querySelector('label');
                if (label) {
                    if (label.textContent.includes('Tujuan Kunjungan') || label.textContent.includes('tujuan kunjungan')) {
                        radio.addEventListener('change', function() {
                            toggleJenisKasus(this.value);
                        });
                    } else if (label.textContent.includes('Jenis Kasus') || label.textContent.includes('jenis kasus')) {
                        radio.addEventListener('change', function() {
                            toggleJenisKasusFields(this.value);
                        });
                    } else if (label.textContent.includes('tujuan visit ke kemitraan')) {
                        radio.addEventListener('change', function() {
                            toggleNamaKantor(this.value);
                        });
                    }
                }
            });
            
            // Trigger initial state for already selected radio buttons
            const selectedTujuanVisit = dynamicContent.querySelector('input[type="radio"]:checked');
            if (selectedTujuanVisit) {
                const label = selectedTujuanVisit.closest('.form-group').querySelector('label');
                if (label && label.textContent.includes('tujuan visit ke kemitraan')) {
                    toggleNamaKantor(selectedTujuanVisit.value);
                }
            }
            
            // Check initial state for Tujuan Kunjungan in dynamic content
            const selectedTujuanKunjungan = dynamicContent.querySelector('input[type="radio"]:checked');
            if (selectedTujuanKunjungan) {
                const label = selectedTujuanKunjungan.closest('.form-group').querySelector('label');
                if (label && (label.textContent.includes('Tujuan Kunjungan') || label.textContent.includes('tujuan kunjungan'))) {
                    toggleJenisKasus(selectedTujuanKunjungan.value);
                }
            }
            
            // Check initial state for Jenis Kasus in dynamic content
            const selectedJenisKasus = dynamicContent.querySelector('input[type="radio"]:checked');
            if (selectedJenisKasus) {
                const label = selectedJenisKasus.closest('.form-group').querySelector('label');
                if (label && (label.textContent.includes('Jenis Kasus') || label.textContent.includes('jenis kasus'))) {
                    toggleJenisKasusFields(selectedJenisKasus.value);
                }
            }
        }
        
        function toggleNamaKantor(tujuanVisit) {
            const formGroups = document.querySelectorAll('.form-group');
            
            formGroups.forEach(function(group) {
                const label = group.querySelector('label');
                if (label && (label.textContent.includes('Pilih nama kantor') || label.textContent.includes('nama kantor'))) {
                    if (tujuanVisit === 'Kantor Kemitraan') {
                        group.style.display = 'none';
                        clearAndDisableInputs(group);
                    } else if (tujuanVisit === 'Peternak Kemitraan') {
                        group.style.display = 'block';
                        restoreRequiredAttributes(group);
                    }
                }
            });
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            const initialRadios = document.querySelectorAll('#initialQuestions input[type="radio"]');
            
            initialRadios.forEach(function(radio) {
                const label = radio.closest('.form-group').querySelector('label');
                if (label) {
                    if (label.textContent.includes('Tujuan Kunjungan') || label.textContent.includes('tujuan kunjungan')) {
                        radio.addEventListener('change', function() {
                            toggleJenisKasus(this.value);
                        });
                    } else if (label.textContent.includes('Jenis Kasus') || label.textContent.includes('jenis kasus')) {
                        radio.addEventListener('change', function() {
                            toggleJenisKasusFields(this.value);
                        });
                    } else if (label.textContent.includes('tujuan visit ke kemitraan')) {
                        radio.addEventListener('change', function() {
                            toggleNamaKantor(this.value);
                        });
                    }
                }
            });
            
            // Trigger initial state for already selected radio buttons in initial questions
            const selectedInitialTujuanVisit = document.querySelector('#initialQuestions input[type="radio"]:checked');
            if (selectedInitialTujuanVisit) {
                const label = selectedInitialTujuanVisit.closest('.form-group').querySelector('label');
                if (label && (label.textContent.includes('tujuan visit ke kemitraan'))) {
                    toggleNamaKantor(selectedInitialTujuanVisit.value);
                }
            }
            
            const selectedTujuanKunjungan = document.querySelector('#initialQuestions input[type="radio"]:checked');
            if (selectedTujuanKunjungan) {
                const label = selectedTujuanKunjungan.closest('.form-group').querySelector('label');
                if (label && (label.textContent.includes('Tujuan Kunjungan') || label.textContent.includes('tujuan kunjungan'))) {
                    toggleJenisKasus(selectedTujuanKunjungan.value);
                    
                    // Check if any jenis kasus is already selected
                    const selectedJenisKasus = document.querySelector('#initialQuestions input[type="radio"]:checked');
                    if (selectedJenisKasus) {
                        const jenisKasusLabel = selectedJenisKasus.closest('.form-group').querySelector('label');
                        if (jenisKasusLabel && (jenisKasusLabel.textContent.includes('Jenis Kasus') || jenisKasusLabel.textContent.includes('jenis kasus'))) {
                            toggleJenisKasusFields(selectedJenisKasus.value);
                        }
                    }
                }
            }
        });
    </script>
</body>
</html> 
