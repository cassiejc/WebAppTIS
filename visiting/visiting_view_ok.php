<!DOCTYPE html>
<html>
<head>
    <title>Form Visiting <?php echo $visiting_type; ?></title>
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
        
        /* Hide jenis kasus fields by default */
        .jenis-kasus-hidden {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            height: 0 !important;
            overflow: hidden !important;
            margin: 0 !important;
            padding: 0 !important;
            position: absolute !important;
            left: -9999px !important;
            top: -9999px !important;
            z-index: -9999 !important;
        }
    </style>
</head>
<body>
    <h2>Form Visiting <?php echo $visiting_type; ?> - <?php echo $current_sub_area['nama_sub_area']; ?></h2>

    <form method="post" action="" id="visitingForm">
        <input type="hidden" name="action" value="<?php echo $action_type; ?>">
        
        <div id="questions">
            <?php if (!empty($questions)): ?>
                <?php foreach ($questions as $q): ?>
                    <?php 
                    // Check if this is a jenis kasus question
                    $isJenisKasus = (isset($q['field_name']) && $q['field_name'] == 'jenis_kasus') ||
                                   (isset($q['question_text']) && stripos($q['question_text'], 'jenis kasus') !== false);
                    
                    // Check if this is a jenis kasus dropdown field
                    $isJenisKasusDropdown = false;
                    if (isset($q['question_text'])) {
                        $questionText = strtolower($q['question_text']);
                        $dropdownTypes = ['bacterial', 'virus', 'parasit', 'jamur', 'lain-lain', 'lain_lain'];
                        $isJenisKasusDropdown = array_filter($dropdownTypes, function($type) use ($questionText) {
                            return strpos($questionText, $type) !== false;
                        });
                    }
                    
                    // Skip the kunjungan_ke field since we're using submenu now
                    if (isset($q['field_name']) && $q['field_name'] == 'kunjungan_ke') {
                        continue;
                    }
                    
                    $hideClass = '';
                    $hideStyle = '';
                    if ($isJenisKasus) {
                        $hideClass = 'jenis-kasus-hidden';
                        $hideStyle = 'style="display: none; visibility: hidden; opacity: 0; height: 0; overflow: hidden; margin: 0; padding: 0; position: absolute; left: -9999px; top: -9999px; z-index: -9999;"';
                    } elseif ($isJenisKasusDropdown) {
                        $hideClass = 'jenis-kasus-hidden';
                        $hideStyle = 'style="display: none; visibility: hidden; opacity: 0; height: 0; overflow: hidden; margin: 0; padding: 0; position: absolute; left: -9999px; top: -9999px; z-index: -9999;"';
                    }
                    ?>
                    <div class="form-group <?= $hideClass ?>" <?= $hideStyle ?>>
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
                                               <?= ($isJenisKasus || $isJenisKasusDropdown) ? 'disabled' : '' ?>
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
                                   <?= !empty($q['required']) ? 'required' : '' ?>
                                   <?= ($isJenisKasus || $isJenisKasusDropdown) ? 'disabled' : '' ?>>
                        <?php elseif ($q['type'] == 'textarea'): ?>
                            <textarea name="q<?= $q['questions_id'] ?>" 
                                      placeholder="Masukkan jawaban Anda"
                                      <?= !empty($q['required']) ? 'required' : '' ?>
                                      <?= ($isJenisKasus || $isJenisKasusDropdown) ? 'disabled' : '' ?>></textarea>
                        <?php elseif ($q['type'] == 'select' && !empty($q['options'])): ?>
                             <select name="q<?= $q['questions_id'] ?>" 
                                     <?= !empty($q['required']) ? 'required' : '' ?>
                                     <?= ($isJenisKasus || $isJenisKasusDropdown) ? 'disabled' : '' ?>>
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
                                   <?= !empty($q['required']) ? 'required' : '' ?>
                                   <?= ($isJenisKasus || $isJenisKasusDropdown) ? 'disabled' : '' ?>>
                        <?php elseif ($q['type'] == 'checkbox' && !empty($q['options'])): ?>
                            <div class="checkbox-group">
                                <?php foreach ($q['options'] as $opt): ?>
                                    <label>
                                        <input type="checkbox" 
                                               name="q<?= $q['questions_id'] ?>[]" 
                                               value="<?= $opt['option_text'] ?>"
                                               <?= ($isJenisKasus || $isJenisKasusDropdown) ? 'disabled' : '' ?>> 
                                        <?= $opt['option_text'] ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-questions">Tidak ada pertanyaan untuk form visiting <?php echo $visiting_type; ?>.</p>
            <?php endif; ?>
        </div>
        
        <button type="submit" class="btn-submit" id="submitBtn">
            <?php echo ($action_type === 'next') ? 'Next' : 'Submit'; ?>
        </button>
    </form>

    <script>
        // Field mapping for jenis kasus
        const JENIS_KASUS_MAPPING = {
            'Bacterial': 'bacterial',
            'Viral': 'virus', 
            'Parasit': 'parasit',
            'Jamur': 'jamur',
            'Lain-lain': 'lain_lain',
            'Lambat puncak': null
        };

        // Utility functions
        const FormUtils = {
            // Check if Kasus is selected in tujuan kunjungan
            isKasusSelected: function() {
                const radios = document.querySelectorAll('input[type="radio"]');
                for (let radio of radios) {
                    const label = radio.closest('.form-group')?.querySelector('label');
                    if (label && (label.textContent.includes('Tujuan Kunjungan') || label.textContent.includes('tujuan kunjungan'))) {
                        if (radio.checked && radio.value === 'Kasus') return true;
                    }
                }
                return false;
            },

            // Hide form group with multiple approaches
            hideFormGroup: function(group) {
                group.classList.add('jenis-kasus-hidden');
                Object.assign(group.style, {
                    display: 'none',
                    visibility: 'hidden',
                    opacity: '0',
                    height: '0',
                    overflow: 'hidden',
                    margin: '0',
                    padding: '0',
                    position: 'absolute',
                    left: '-9999px',
                    top: '-9999px',
                    zIndex: '-9999'
                });
                
                // Clear and disable inputs
                const inputs = group.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    input.disabled = true;
                    input.removeAttribute('required');
                    if (input.type === 'radio' || input.type === 'checkbox') {
                        input.checked = false;
                    } else {
                        input.value = '';
                    }
                });
            },

            // Show form group
            showFormGroup: function(group) {
                group.classList.remove('jenis-kasus-hidden');
                Object.assign(group.style, {
                    display: 'block',
                    visibility: 'visible',
                    opacity: '1',
                    height: 'auto',
                    overflow: 'visible',
                    margin: '',
                    padding: '',
                    position: '',
                    left: '',
                    top: '',
                    zIndex: ''
                });
                
                // Enable inputs and restore required if needed
                const inputs = group.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    input.disabled = false;
                    const label = group.querySelector('label');
                    if (label && label.innerHTML.includes('<span class="required">*</span>')) {
                        input.setAttribute('required', 'required');
                    }
                });
            },

            // Find form group by label text
            findFormGroupByLabel: function(labelText, container = document) {
                const groups = container.querySelectorAll('.form-group');
                for (let group of groups) {
                    const label = group.querySelector('label');
                    if (label && label.textContent.toLowerCase().includes(labelText.toLowerCase())) {
                        return group;
                    }
                }
                return null;
            },

            // Hide all jenis kasus related fields
            hideAllJenisKasusFields: function() {
                const container = document.querySelector('#questions');
                if (!container) return;
                
                const fieldTypes = ['bacterial', 'virus', 'parasit', 'jamur', 'lain-lain', 'lain_lain', 'lambat puncak'];
                
                const groups = container.querySelectorAll('.form-group');
                groups.forEach(group => {
                    const label = group.querySelector('label');
                    if (label) {
                        const labelText = label.textContent.toLowerCase();
                        if (fieldTypes.some(type => labelText.includes(type))) {
                            this.hideFormGroup(group);
                        }
                    }
                });
            }
        };

        // Main hiding function
        function hideJenisKasusFields() {
            if (FormUtils.isKasusSelected()) return;
            
            const container = document.querySelector('#questions');
            if (!container) return;
            
            const groups = container.querySelectorAll('.form-group');
            groups.forEach(group => {
                const label = group.querySelector('label');
                if (!label) return;
                
                const labelText = label.textContent.toLowerCase();
                
                // Hide jenis kasus section (main radio group)
                if (labelText.includes('jenis kasus') && 
                    !['bacterial', 'virus', 'parasit', 'jamur', 'lain-lain', 'lain_lain'].some(type => labelText.includes(type))) {
                    FormUtils.hideFormGroup(group);
                }
                
                // Hide jenis kasus dropdown fields
                if (['bacterial', 'virus', 'parasit', 'jamur', 'lain-lain', 'lain_lain'].some(type => labelText.includes(type))) {
                    FormUtils.hideFormGroup(group);
                }
            });
        }

        function toggleJenisKasus(tujuanKunjungan) {
            const container = document.querySelector('#questions');
            if (!container) return;
            
            const jenisKasusGroup = FormUtils.findFormGroupByLabel('jenis kasus', container);
            if (!jenisKasusGroup) return;
            
            if (tujuanKunjungan === 'Monitoring') {
                FormUtils.hideFormGroup(jenisKasusGroup);
                FormUtils.hideAllJenisKasusFields();
            } else if (tujuanKunjungan === 'Kasus') {
                FormUtils.showFormGroup(jenisKasusGroup);
                FormUtils.hideAllJenisKasusFields();
            }
        }

        function toggleJenisKasusFields(jenisKasus) {
            const targetFieldName = JENIS_KASUS_MAPPING[jenisKasus];
            
            if (targetFieldName === null) {
                FormUtils.hideAllJenisKasusFields();
                return;
            }
            
            if (!targetFieldName) return;
            
            const container = document.querySelector('#questions');
            if (!container) return;
            
            // Hide all jenis kasus fields first
            FormUtils.hideAllJenisKasusFields();
            
            // Show only the selected field
            const groups = container.querySelectorAll('.form-group');
            groups.forEach(group => {
                const label = group.querySelector('label');
                if (label) {
                    const labelText = label.textContent.toLowerCase();
                    if (labelText.includes(targetFieldName.toLowerCase()) || 
                        (targetFieldName === 'virus' && labelText.includes('virus')) ||
                        (targetFieldName === 'lain_lain' && (labelText.includes('lain-lain') || labelText.includes('lain_lain')))) {
                        FormUtils.showFormGroup(group);
                    }
                }
            });
        }

        // Initialize hiding on page load
        (function() {
            const initHiding = () => {
                hideJenisKasusFields();
                FormUtils.hideAllJenisKasusFields();
            };
            
            // Run multiple times to ensure it works
            [0, 10, 50, 100, 200, 500, 1000, 2000].forEach(delay => {
                setTimeout(initHiding, delay);
            });
            
            document.addEventListener('DOMContentLoaded', initHiding);
            window.addEventListener('load', initHiding);
            
            // Continuous checking
            setInterval(() => {
                if (!FormUtils.isKasusSelected()) {
                    hideJenisKasusFields();
                }
            }, 1000);
        })();

        // Initialize form on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize conditional logic for questions
            const radios = document.querySelectorAll('#questions input[type="radio"]');
            
            // Add event listeners for tujuan kunjungan
            radios.forEach(radio => {
                const label = radio.closest('.form-group')?.querySelector('label');
                if (label && (label.textContent.includes('Tujuan Kunjungan') || label.textContent.includes('tujuan kunjungan'))) {
                    radio.addEventListener('change', function() {
                        toggleJenisKasus(this.value);
                    });
                }
                
                // Add event listeners for jenis kasus
                if (label && (label.textContent.includes('Jenis Kasus') || label.textContent.includes('jenis kasus'))) {
                    radio.addEventListener('change', function() {
                        toggleJenisKasusFields(this.value);
                    });
                }
            });
            
            // Trigger initial state
            const selectedTujuanKunjungan = Array.from(radios).find(radio => {
                const label = radio.closest('.form-group')?.querySelector('label');
                return label && (label.textContent.includes('Tujuan Kunjungan') || label.textContent.includes('tujuan kunjungan')) && radio.checked;
            });
            
            if (selectedTujuanKunjungan) {
                selectedTujuanKunjungan.dispatchEvent(new Event('change'));
            }
        });
    </script>
</body>
</html>
