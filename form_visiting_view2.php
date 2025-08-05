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
        
        .jenis-kasus-dropdown.show select,
        .jenis-kasus-dropdown.show input,
        .jenis-kasus-dropdown.show textarea {
            pointer-events: auto !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
    </style>
    <script>
        // Global flag to prevent hiding dropdowns when user is interacting
        let isUserInteractingWithDropdown = false;
        
        // Global functions for hiding fields
        function hideJenisKasusFields() {
            // Don't hide if user is interacting with dropdown
            if (isUserInteractingWithDropdown) {
                return;
            }
            
            const allFormGroups = document.querySelectorAll('.form-group');
            allFormGroups.forEach(function(group) {
                const label = group.querySelector('label');
                if (label) {
                    const labelText = label.textContent.toLowerCase();
                    // Only hide dropdown fields, not the jenis kasus question
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
                                    // This field should be visible, don't hide it
                                    return;
                                }
                            }
                        }
                        
                        // Check if this dropdown has a selected value - if so, don't hide it
                        const selectElement = group.querySelector('select');
                        if (selectElement && selectElement.value && selectElement.value !== '') {
                            // Don't hide dropdowns that have been selected
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
        
        function checkInitialTujuanKunjungan() {
            // Check if "Kasus" is selected in Tujuan Kunjungan
            const kasusRadio = document.querySelector('input[type="radio"][value="Kasus"]:checked');
            const monitoringRadio = document.querySelector('input[type="radio"][value="Monitoring"]:checked');
            
            // Hide Jenis Kasus question by default unless "Kasus" is selected
            const formGroups = document.querySelectorAll('.form-group');
            formGroups.forEach(function(group) {
                const groupLabel = group.querySelector('label');
                if (groupLabel && groupLabel.textContent.toLowerCase().includes('jenis kasus')) {
                    if (kasusRadio) {
                        // Show Jenis Kasus question if Kasus is selected
                        group.style.display = 'block';
                        const inputs = group.querySelectorAll('input, select, textarea');
                        inputs.forEach(function(input) {
                            input.disabled = false;
                            if (groupLabel.innerHTML.includes('<span class="required">*</span>')) {
                                input.setAttribute('required', 'required');
                            }
                        });
                        
                        // Check if any jenis kasus is already selected
                        const selectedJenisKasus = group.querySelector('input[type="radio"]:checked');
                        if (selectedJenisKasus) {
                            toggleJenisKasusFields(selectedJenisKasus.value);
                        }
                    } else {
                        // Hide Jenis Kasus question if Kasus is not selected
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
        
        // Hide jenis kasus dropdown fields on page load (but not the jenis kasus question)
        (function() {
            function forceHideFields() {
                hideJenisKasusFields();
                checkInitialTujuanKunjungan();
            }
            
            // Set up MutationObserver to watch for DOM changes
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                        // New nodes were added, hide dropdown fields
                        setTimeout(forceHideFields, 10);
                        setTimeout(forceHideFields, 50);
                        setTimeout(forceHideFields, 100);
                    }
                });
            });
            
            // Start observing when DOM is ready
            function startObserving() {
                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });
                
                // Add specific observer for form changes
                const formObserver = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'disabled') {
                            // A disabled attribute was changed, check if we need to enable dropdown
                            setTimeout(ensureDropdownEnabled, 10);
                        }
                    });
                });
                
                // Observe all form elements for disabled attribute changes
                const formElements = document.querySelectorAll('input, select, textarea');
                formElements.forEach(function(element) {
                    formObserver.observe(element, {
                        attributes: true,
                        attributeFilter: ['disabled']
                    });
                });
                
                // Add event listeners for user interactions
                document.addEventListener('click', function() {
                    // Don't hide fields if user is interacting with dropdown
                    if (!isUserInteractingWithDropdown) {
                        setTimeout(forceHideFields, 10);
                    }
                });
                
                document.addEventListener('change', function() {
                    // Don't hide fields if user is interacting with dropdown
                    if (!isUserInteractingWithDropdown) {
                        setTimeout(forceHideFields, 10);
                    }
                    
                    // Check if jenis kasus was selected
                    const selectedJenisKasus = document.querySelector('input[type="radio"]:checked');
                    if (selectedJenisKasus) {
                        const label = selectedJenisKasus.closest('.form-group').querySelector('label');
                        if (label && (label.textContent.includes('Jenis Kasus') || label.textContent.includes('jenis kasus'))) {
                            setTimeout(function() {
                                toggleJenisKasusFields(selectedJenisKasus.value);
                                ensureDropdownEnabled();
                            }, 100);
                        }
                    }
                });
                
                // Add specific listener for radio button clicks
                document.addEventListener('click', function(e) {
                    if (e.target.type === 'radio') {
                        const label = e.target.closest('.form-group').querySelector('label');
                        if (label && (label.textContent.includes('Jenis Kasus') || label.textContent.includes('jenis kasus'))) {
                            setTimeout(function() {
                                toggleJenisKasusFields(e.target.value);
                                ensureDropdownEnabled();
                            }, 100);
                        }
                    }
                });
                
                // Add focus listener to ensure dropdown is enabled when user tries to interact
                document.addEventListener('focus', function(e) {
                    if (e.target.tagName === 'SELECT' || e.target.type === 'select-one') {
                        const label = e.target.closest('.form-group').querySelector('label');
                        if (label) {
                            const labelText = label.textContent.toLowerCase();
                            if (labelText.includes('bacterial') || labelText.includes('virus') || 
                                labelText.includes('parasit') || labelText.includes('jamur') || 
                                labelText.includes('lain-lain') || labelText.includes('lain_lain')) {
                                
                                // Set flag to prevent hiding
                                isUserInteractingWithDropdown = true;
                                
                                // Ensure this dropdown is enabled
                                e.target.disabled = false;
                                e.target.removeAttribute('disabled');
                                e.target.closest('.form-group').classList.remove('jenis-kasus-dropdown');
                                e.target.closest('.form-group').classList.add('show');
                                
                                // Ensure dropdown stays open
                                ensureDropdownStaysOpen();
                            }
                        }
                    }
                }, true);
                
                // Add blur listener to clear flag when user finishes interacting
                document.addEventListener('blur', function(e) {
                    if (e.target.tagName === 'SELECT' || e.target.type === 'select-one') {
                        const label = e.target.closest('.form-group').querySelector('label');
                        if (label) {
                            const labelText = label.textContent.toLowerCase();
                            if (labelText.includes('bacterial') || labelText.includes('virus') || 
                                labelText.includes('parasit') || labelText.includes('jamur') || 
                                labelText.includes('lain-lain') || labelText.includes('lain_lain')) {
                                
                                // Clear flag after a short delay to allow for selection
                                setTimeout(function() {
                                    isUserInteractingWithDropdown = false;
                                }, 500);
                            }
                        }
                    }
                }, true);
                
                // Add mousedown listener to set flag when user clicks dropdown
                document.addEventListener('mousedown', function(e) {
                    if (e.target.tagName === 'SELECT' || e.target.type === 'select-one') {
                        const label = e.target.closest('.form-group').querySelector('label');
                        if (label) {
                            const labelText = label.textContent.toLowerCase();
                            if (labelText.includes('bacterial') || labelText.includes('virus') || 
                                labelText.includes('parasit') || labelText.includes('jamur') || 
                                labelText.includes('lain-lain') || labelText.includes('lain_lain')) {
                                
                                // Set flag to prevent hiding
                                isUserInteractingWithDropdown = true;
                                
                                // Ensure this dropdown is enabled
                                e.target.disabled = false;
                                e.target.removeAttribute('disabled');
                                e.target.closest('.form-group').classList.remove('jenis-kasus-dropdown');
                                e.target.closest('.form-group').classList.add('show');
                                
                                // Ensure dropdown stays open
                                ensureDropdownStaysOpen();
                            }
                        }
                    }
                });
                
                // Add change listener to clear flag when user makes a selection
                document.addEventListener('change', function(e) {
                    if (e.target.tagName === 'SELECT' || e.target.type === 'select-one') {
                        const label = e.target.closest('.form-group').querySelector('label');
                        if (label) {
                            const labelText = label.textContent.toLowerCase();
                            if (labelText.includes('bacterial') || labelText.includes('virus') || 
                                labelText.includes('parasit') || labelText.includes('jamur') || 
                                labelText.includes('lain-lain') || labelText.includes('lain_lain')) {
                                
                                // Keep the dropdown visible after selection
                                e.target.closest('.form-group').classList.remove('jenis-kasus-dropdown');
                                e.target.closest('.form-group').classList.add('show');
                                e.target.closest('.form-group').style.display = 'block';
                                e.target.closest('.form-group').style.visibility = 'visible';
                                e.target.closest('.form-group').style.opacity = '1';
                                
                                // Ensure dropdown stays visible after selection
                                setTimeout(function() {
                                    ensureDropdownStaysVisibleAfterSelection();
                                }, 100);
                                
                                // Clear flag after a longer delay to ensure selection is complete
                                setTimeout(function() {
                                    isUserInteractingWithDropdown = false;
                                }, 2000);
                            }
                        }
                    }
                });
                
                // Add mouseenter listener to keep dropdown open when hovering
                document.addEventListener('mouseenter', function(e) {
                    if (e.target.tagName === 'SELECT' || e.target.type === 'select-one') {
                        const label = e.target.closest('.form-group').querySelector('label');
                        if (label) {
                            const labelText = label.textContent.toLowerCase();
                            if (labelText.includes('bacterial') || labelText.includes('virus') || 
                                labelText.includes('parasit') || labelText.includes('jamur') || 
                                labelText.includes('lain-lain') || labelText.includes('lain_lain')) {
                                
                                // Set flag to prevent hiding
                                isUserInteractingWithDropdown = true;
                                
                                // Ensure this dropdown is enabled and visible
                                e.target.disabled = false;
                                e.target.removeAttribute('disabled');
                                e.target.closest('.form-group').classList.remove('jenis-kasus-dropdown');
                                e.target.closest('.form-group').classList.add('show');
                                e.target.closest('.form-group').style.display = 'block';
                                e.target.closest('.form-group').style.visibility = 'visible';
                                e.target.closest('.form-group').style.opacity = '1';
                            }
                        }
                    }
                });
                
                // Add mouseleave listener to clear flag when user leaves dropdown
                document.addEventListener('mouseleave', function(e) {
                    if (e.target.tagName === 'SELECT' || e.target.type === 'select-one') {
                        const label = e.target.closest('.form-group').querySelector('label');
                        if (label) {
                            const labelText = label.textContent.toLowerCase();
                            if (labelText.includes('bacterial') || labelText.includes('virus') || 
                                labelText.includes('parasit') || labelText.includes('jamur') || 
                                labelText.includes('lain-lain') || labelText.includes('lain_lain')) {
                                
                                // Clear flag after a delay to allow for selection
                                setTimeout(function() {
                                    isUserInteractingWithDropdown = false;
                                }, 1000);
                            }
                        }
                    }
                });
                
                // Add interval to continuously ensure dropdowns stay open
                setInterval(function() {
                    if (isUserInteractingWithDropdown) {
                        ensureDropdownStaysOpen();
                    }
                }, 100);
                
                // Add interval to ensure dropdowns stay visible after selection
                setInterval(function() {
                    ensureDropdownStaysVisibleAfterSelection();
                }, 500);
                
                // Add event listener for any form changes to ensure dropdowns stay visible
                document.addEventListener('input', function(e) {
                    if (e.target.tagName === 'SELECT' || e.target.type === 'select-one') {
                        const label = e.target.closest('.form-group').querySelector('label');
                        if (label) {
                            const labelText = label.textContent.toLowerCase();
                            if (labelText.includes('bacterial') || labelText.includes('virus') || 
                                labelText.includes('parasit') || labelText.includes('jamur') || 
                                labelText.includes('lain-lain') || labelText.includes('lain_lain')) {
                                
                                // Ensure dropdown stays visible after any input
                                setTimeout(function() {
                                    ensureDropdownStaysVisibleAfterSelection();
                                }, 50);
                            }
                        }
                    }
                });
                
                document.addEventListener('input', function() {
                    // Don't hide fields if user is interacting with dropdown
                    if (!isUserInteractingWithDropdown) {
                        setTimeout(forceHideFields, 10);
                    }
                });
                
                // Hide fields when window gets focus
                window.addEventListener('focus', function() {
                    setTimeout(forceHideFields, 10);
                });
                
                // Hide fields when page becomes visible
                document.addEventListener('visibilitychange', function() {
                    if (!document.hidden) {
                        setTimeout(forceHideFields, 10);
                    }
                });
                
                // Hide fields on scroll and resize
                window.addEventListener('scroll', function() {
                    setTimeout(forceHideFields, 10);
                });
                
                window.addEventListener('resize', function() {
                    setTimeout(forceHideFields, 10);
                });
                
                // Hide fields when form changes
                const form = document.getElementById('visitingForm');
                if (form) {
                    form.addEventListener('change', function() {
                        setTimeout(forceHideFields, 10);
                    });
                }
                
                // Hide fields when radio buttons change
                document.addEventListener('change', function(event) {
                    if (event.target.type === 'radio') {
                        setTimeout(forceHideFields, 10);
                        setTimeout(forceHideFields, 50);
                        setTimeout(forceHideFields, 100);
                    }
                });
            }
            
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    forceHideFields();
                    startObserving();
                    
                    // Run multiple times with different delays
                    setTimeout(forceHideFields, 10);
                    setTimeout(forceHideFields, 50);
                    setTimeout(forceHideFields, 100);
                    setTimeout(forceHideFields, 200);
                    setTimeout(forceHideFields, 500);
                    setTimeout(forceHideFields, 1000);
                    
                    // Run continuously for the first few seconds
                    let count = 0;
                    const interval = setInterval(function() {
                        forceHideFields();
                        count++;
                        if (count >= 30) { // Run for 3 seconds (30 * 100ms)
                            clearInterval(interval);
                        }
                    }, 100);
                    
                    // Continuous monitoring every 2 seconds
                    setInterval(forceHideFields, 2000);
                });
            } else {
                forceHideFields();
                startObserving();
                
                // Run multiple times with different delays
                setTimeout(forceHideFields, 10);
                setTimeout(forceHideFields, 50);
                setTimeout(forceHideFields, 100);
                setTimeout(forceHideFields, 200);
                setTimeout(forceHideFields, 500);
                setTimeout(forceHideFields, 1000);
                
                // Run continuously for the first few seconds
                let count = 0;
                const interval = setInterval(function() {
                    forceHideFields();
                    count++;
                    if (count >= 30) { // Run for 3 seconds (30 * 100ms)
                        clearInterval(interval);
                    }
                }, 100);
                
                // Continuous monitoring every 2 seconds
                setInterval(forceHideFields, 2000);
            }
        })();
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
                    
                    setTimeout(function() {
                        hideJenisKasusFields();
                        checkInitialTujuanKunjungan();
                    }, 200);
                    
                    // Run continuously for a few seconds after dynamic content loads
                    let dynamicCount = 0;
                    const dynamicInterval = setInterval(function() {
                        hideJenisKasusFields();
                        checkInitialTujuanKunjungan();
                        dynamicCount++;
                        if (dynamicCount >= 25) { // Run for 2.5 seconds (25 * 100ms)
                            clearInterval(dynamicInterval);
                        }
                    }, 100);
                    
                    // Additional continuous monitoring for dynamic content
                    setInterval(function() {
                        hideJenisKasusFields();
                        checkInitialTujuanKunjungan();
                    }, 1500);
                    
                    // Check and apply correct state after dynamic content loads
                    setTimeout(function() {
                        // Check if any jenis kasus is selected in dynamic content
                        const dynamicContent = document.getElementById('dynamicFormContent');
                        const selectedJenisKasus = dynamicContent.querySelector('input[type="radio"]:checked');
                        if (selectedJenisKasus) {
                            const label = selectedJenisKasus.closest('.form-group').querySelector('label');
                            if (label && (label.textContent.includes('Jenis Kasus') || label.textContent.includes('jenis kasus'))) {
                                toggleJenisKasusFields(selectedJenisKasus.value);
                                ensureDropdownEnabled();
                            }
                        }
                    }, 300);
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
                            // Don't hide dropdowns that have been selected
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
                        
                        // Enable all inputs in this group
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
                    // Only hide dropdown fields, not the jenis kasus question
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
        
        function ensureDropdownEnabled() {
            const selectedJenisKasus = document.querySelector('input[type="radio"]:checked');
            if (selectedJenisKasus) {
                const label = selectedJenisKasus.closest('.form-group').querySelector('label');
                if (label && (label.textContent.includes('Jenis Kasus') || label.textContent.includes('jenis kasus'))) {
                    const fieldMapping = {
                        'Bacterial': 'bacterial',
                        'Viral': 'virus',
                        'Parasit': 'parasit',
                        'Jamur': 'jamur',
                        'Lain-lain': 'lain_lain'
                    };
                    
                    const targetFieldName = fieldMapping[selectedJenisKasus.value];
                    if (targetFieldName) {
                        const formGroups = document.querySelectorAll('.form-group');
                        formGroups.forEach(function(group) {
                            const groupLabel = group.querySelector('label');
                            if (groupLabel) {
                                const labelText = groupLabel.textContent.toLowerCase();
                                if (labelText.includes(targetFieldName.toLowerCase()) || 
                                    (targetFieldName === 'virus' && labelText.includes('virus')) ||
                                    (targetFieldName === 'lain_lain' && (labelText.includes('lain-lain') || labelText.includes('lain_lain')))) {
                                    
                                    // Ensure the field is visible and enabled
                                    group.classList.remove('jenis-kasus-dropdown');
                                    group.classList.add('show');
                                    
                                    const inputs = group.querySelectorAll('input, select, textarea');
                                    inputs.forEach(function(input) {
                                        input.disabled = false;
                                        input.removeAttribute('disabled');
                                        if (groupLabel.innerHTML.includes('<span class="required">*</span>')) {
                                            input.setAttribute('required', 'required');
                                        }
                                    });
                                }
                            }
                        });
                    }
                }
            }
        }

        function ensureDropdownStaysOpen() {
            const selectedJenisKasus = document.querySelector('input[type="radio"]:checked');
            if (selectedJenisKasus) {
                const label = selectedJenisKasus.closest('.form-group').querySelector('label');
                if (label && (label.textContent.includes('Jenis Kasus') || label.textContent.includes('jenis kasus'))) {
                    const fieldMapping = {
                        'Bacterial': 'bacterial',
                        'Viral': 'virus',
                        'Parasit': 'parasit',
                        'Jamur': 'jamur',
                        'Lain-lain': 'lain_lain'
                    };
                    
                    const targetFieldName = fieldMapping[selectedJenisKasus.value];
                    if (targetFieldName) {
                        const formGroups = document.querySelectorAll('.form-group');
                        formGroups.forEach(function(group) {
                            const groupLabel = group.querySelector('label');
                            if (groupLabel) {
                                const labelText = groupLabel.textContent.toLowerCase();
                                if (labelText.includes(targetFieldName.toLowerCase()) || 
                                    (targetFieldName === 'virus' && labelText.includes('virus')) ||
                                    (targetFieldName === 'lain_lain' && (labelText.includes('lain-lain') || labelText.includes('lain_lain')))) {
                                    
                                    // Ensure the field is visible and enabled
                                    group.classList.remove('jenis-kasus-dropdown');
                                    group.classList.add('show');
                                    group.style.display = 'block';
                                    group.style.visibility = 'visible';
                                    group.style.opacity = '1';
                                    
                                    const inputs = group.querySelectorAll('input, select, textarea');
                                    inputs.forEach(function(input) {
                                        input.disabled = false;
                                        input.removeAttribute('disabled');
                                        input.style.pointerEvents = 'auto';
                                        
                                        if (groupLabel.innerHTML.includes('<span class="required">*</span>')) {
                                            input.setAttribute('required', 'required');
                                        }
                                    });
                                }
                            }
                        });
                    }
                }
            }
        }
        
        function ensureDropdownStaysVisibleAfterSelection() {
            const selectedJenisKasus = document.querySelector('input[type="radio"]:checked');
            if (selectedJenisKasus) {
                const label = selectedJenisKasus.closest('.form-group').querySelector('label');
                if (label && (label.textContent.includes('Jenis Kasus') || label.textContent.includes('jenis kasus'))) {
                    const fieldMapping = {
                        'Bacterial': 'bacterial',
                        'Viral': 'virus',
                        'Parasit': 'parasit',
                        'Jamur': 'jamur',
                        'Lain-lain': 'lain_lain'
                    };
                    
                    const targetFieldName = fieldMapping[selectedJenisKasus.value];
                    if (targetFieldName) {
                        const formGroups = document.querySelectorAll('.form-group');
                        formGroups.forEach(function(group) {
                            const groupLabel = group.querySelector('label');
                            if (groupLabel) {
                                const labelText = groupLabel.textContent.toLowerCase();
                                if (labelText.includes(targetFieldName.toLowerCase()) || 
                                    (targetFieldName === 'virus' && labelText.includes('virus')) ||
                                    (targetFieldName === 'lain_lain' && (labelText.includes('lain-lain') || labelText.includes('lain_lain')))) {
                                    
                                    // Ensure the field is visible and enabled
                                    group.classList.remove('jenis-kasus-dropdown');
                                    group.classList.add('show');
                                    group.style.display = 'block';
                                    group.style.visibility = 'visible';
                                    group.style.opacity = '1';
                                    
                                    const inputs = group.querySelectorAll('input, select, textarea');
                                    inputs.forEach(function(input) {
                                        input.disabled = false;
                                        input.removeAttribute('disabled');
                                        input.style.pointerEvents = 'auto';
                                        
                                        if (groupLabel.innerHTML.includes('<span class="required">*</span>')) {
                                            input.setAttribute('required', 'required');
                                        }
                                    });
                                }
                            }
                        });
                    }
                }
            }
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
                    ensureDropdownEnabled();
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
                            ensureDropdownEnabled();
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
                            ensureDropdownEnabled();
                        }
                    }
                }
            }
            
            // Ensure dropdown is enabled after a short delay
            setTimeout(function() {
                ensureDropdownEnabled();
                ensureDropdownStaysVisibleAfterSelection();
            }, 500);
            
            // Additional timeouts to ensure dropdowns are enabled
            setTimeout(function() {
                ensureDropdownStaysVisibleAfterSelection();
            }, 1000);
            
            setTimeout(function() {
                ensureDropdownStaysVisibleAfterSelection();
            }, 2000);
            
            setTimeout(function() {
                ensureDropdownStaysVisibleAfterSelection();
            }, 3000);
        });
    </script>
</body>
</html> 
