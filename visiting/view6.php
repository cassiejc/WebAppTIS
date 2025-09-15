<!DOCTYPE html>
<html>
<head>
    <title>Visiting <?php echo $visiting_type; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .jenis-kasus-hidden { display: none !important; }
        .required { color: red; }
        .currency-prefix {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            pointer-events: none;
            z-index: 3;
        }
        .currency-input { padding-left: 35px !important; }
        
        /* Loading overlay styles */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        .loading-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 2s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-light">
    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-content">
            <div class="spinner"></div>
            <h5>Mengambil Lokasi...</h5>
            <p>Mohon tunggu sebentar</p>
        </div>
    </div>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12 col-lg-8">
                <h2 class="mb-4 text-dark fw-bold">Visiting <?php echo $visiting_type; ?> - <?php echo $current_sub_area['nama_sub_area']; ?></h2>

                <form method="post" action="" id="visitingForm" class="needs-validation" novalidate>
                    <input type="hidden" name="action" value="<?php echo $action_type; ?>">
                    <!-- Hidden location inputs -->
                    <input type="hidden" id="userLatitude" name="user_latitude" value="">
                    <input type="hidden" id="userLongitude" name="user_longitude" value="">
                    <input type="hidden" id="userAddress" name="user_address" value="">
                    
                    <div id="questions">
                        <?php if (!empty($questions)): ?>
                            <?php foreach ($questions as $q): ?>
                                <?php 
                                $isJenisKasus = (isset($q['field_name']) && $q['field_name'] == 'jenis_kasus') ||
                                               (isset($q['question_text']) && stripos($q['question_text'], 'jenis kasus') !== false);
                                
                                $isJenisKasusDropdown = false;
                                if (isset($q['question_text'])) {
                                    $questionText = strtolower($q['question_text']);
                                    $dropdownTypes = ['bacterial', 'virus', 'parasit', 'jamur', 'lain-lain', 'lain_lain'];
                                    $isJenisKasusDropdown = array_filter($dropdownTypes, function($type) use ($questionText) {
                                        return strpos($questionText, $type) !== false;
                                    });
                                }
                                
                                if (isset($q['field_name']) && $q['field_name'] == 'kunjungan_ke') {
                                    continue;
                                }
                                
                                $isCurrencyField = (isset($q['field_name']) && $q['field_name'] == 'harga_live_bird') ||
                                                 (isset($q['question_text']) && stripos($q['question_text'], 'harga live bird') !== false);
                                
                                $hideClass = ($isJenisKasus || $isJenisKasusDropdown) ? 'jenis-kasus-hidden' : '';
                                ?>
                                <div class="mb-4 <?= $hideClass ?>">
                                    <label class="form-label fw-semibold text-dark mb-2">
                                        <?= $q['question_text'] ?>
                                        <?php if (!empty($q['required'])): ?> 
                                            <span class="required">*</span> 
                                        <?php endif; ?>
                                    </label>
                                    
                                    <?php if ($q['type'] == 'radio' && !empty($q['options'])): ?>
                                        <div class="d-flex flex-column gap-2">
                                            <?php foreach ($q['options'] as $opt): ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" 
                                                           type="radio" 
                                                           name="q<?= $q['questions_id'] ?>" 
                                                           id="q<?= $q['questions_id'] ?>_<?= $opt['option_text'] ?>"
                                                           value="<?= $opt['option_text'] ?>" 
                                                           <?= !empty($q['required']) ? 'required' : '' ?>
                                                           <?= ($isJenisKasus || $isJenisKasusDropdown) ? 'disabled' : '' ?>
                                                           <?php if ($q['field_name'] == 'tujuan_kunjungan'): ?>onchange="toggleJenisKasus(this.value)"<?php endif; ?>
                                                           <?php if ($q['field_name'] == 'jenis_kasus'): ?>onchange="toggleJenisKasusFields(this.value)"<?php endif; ?>>
                                                    <label class="form-check-label" for="q<?= $q['questions_id'] ?>_<?= $opt['option_text'] ?>">
                                                        <?= $opt['option_text'] ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        
                                    <?php elseif ($q['type'] == 'text'): ?>
                                        <?php if ($isCurrencyField): ?>
                                            <div class="position-relative" style="max-width: 400px;">
                                                <span class="currency-prefix">Rp</span>
                                                <input type="text" 
                                                       class="form-control currency-field currency-input" 
                                                       name="q<?= $q['questions_id'] ?>" 
                                                       placeholder="0"
                                                       data-currency="true"
                                                       <?= !empty($q['required']) ? 'required' : '' ?>
                                                       <?= ($isJenisKasus || $isJenisKasusDropdown) ? 'disabled' : '' ?>>
                                            </div>
                                        <?php else: ?>
                                            <input type="text" 
                                                   class="form-control" 
                                                   style="max-width: 400px;"
                                                   name="q<?= $q['questions_id'] ?>" 
                                                   placeholder="Masukkan jawaban Anda"
                                                   <?= !empty($q['required']) ? 'required' : '' ?>
                                                   <?= ($isJenisKasus || $isJenisKasusDropdown) ? 'disabled' : '' ?>>
                                        <?php endif; ?>
                                           
                                    <?php elseif ($q['type'] == 'textarea'): ?>
                                        <textarea class="form-control auto-resize-textarea" 
                                                  style="max-width: 400px; min-height: 80px; resize: none;"
                                                  name="q<?= $q['questions_id'] ?>" 
                                                  placeholder="Masukkan jawaban Anda"
                                                  <?= !empty($q['required']) ? 'required' : '' ?>
                                                  <?= ($isJenisKasus || $isJenisKasusDropdown) ? 'disabled' : '' ?>></textarea>
                                              
                                    <?php elseif ($q['type'] == 'select' && !empty($q['options'])): ?>
                                        <select class="form-select" 
                                                style="max-width: 400px;"
                                                name="q<?= $q['questions_id'] ?>" 
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
                                               class="form-control" 
                                               style="max-width: 400px;"
                                               name="q<?= $q['questions_id'] ?>" 
                                               <?= !empty($q['required']) ? 'required' : '' ?>
                                               <?= ($isJenisKasus || $isJenisKasusDropdown) ? 'disabled' : '' ?>>
                                           
                                    <?php elseif ($q['type'] == 'checkbox' && !empty($q['options'])): ?>
                                        <div class="d-flex flex-column gap-2">
                                            <?php foreach ($q['options'] as $opt): ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           name="q<?= $q['questions_id'] ?>[]" 
                                                           id="q<?= $q['questions_id'] ?>_<?= $opt['option_text'] ?>"
                                                           value="<?= $opt['option_text'] ?>"
                                                           <?= ($isJenisKasus || $isJenisKasusDropdown) ? 'disabled' : '' ?>>
                                                    <label class="form-check-label" for="q<?= $q['questions_id'] ?>_<?= $opt['option_text'] ?>">
                                                        <?= $opt['option_text'] ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-info">Tidak ada pertanyaan untuk form visiting <?php echo $visiting_type; ?>.</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-block mt-4">
                        <button type="submit" class="btn btn-primary px-10" id="submitBtn">
                            <?php echo ($action_type === 'next') ? 'Next' : 'Submit'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto Location functionality
        const AutoLocationManager = {
            getCurrentLocation() {
                return new Promise((resolve, reject) => {
                    if (!navigator.geolocation) {
                        reject(new Error('Geolocation tidak didukung oleh browser ini.'));
                        return;
                    }

                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const lat = position.coords.latitude;
                            const lon = position.coords.longitude;
                            resolve({ lat, lon });
                        },
                        (error) => {
                            let errorMessage = 'Terjadi kesalahan yang tidak diketahui.';
                            
                            switch (error.code) {
                                case error.PERMISSION_DENIED:
                                    errorMessage = 'Akses lokasi ditolak. Silakan izinkan akses lokasi.';
                                    break;
                                case error.POSITION_UNAVAILABLE:
                                    errorMessage = 'Informasi lokasi tidak tersedia.';
                                    break;
                                case error.TIMEOUT:
                                    errorMessage = 'Permintaan lokasi timeout. Coba lagi.';
                                    break;
                            }
                            
                            reject(new Error(errorMessage));
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 15000,
                            maximumAge: 0
                        }
                    );
                });
            },

            async getAddressFromCoords(lat, lon) {
                try {
                    const response = await fetch(`<?php echo base_url('location/reverse_geocode'); ?>?lat=${lat}&lon=${lon}`);
                    const data = await response.json();
                    
                    if (data && data.display_name) {
                        return data.display_name;
                    }
                    return 'Alamat tidak ditemukan';
                } catch (error) {
                    console.error('Error getting address:', error);
                    return 'Gagal mengambil alamat';
                }
            },

            showLoadingOverlay() {
                document.getElementById('loadingOverlay').style.display = 'flex';
            },

            hideLoadingOverlay() {
                document.getElementById('loadingOverlay').style.display = 'none';
            }
        };

        // Currency utilities
        const CurrencyUtils = {
            formatCurrency(value) {
                const numericValue = value.replace(/[^\d]/g, '');
                return numericValue === '' ? '' : numericValue.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            },
            removeCurrencyFormat(value) {
                return value.replace(/,/g, '');
            },
            validateNumericInput(event) {
                if ([8, 9, 27, 13, 46].indexOf(event.keyCode) !== -1 ||
                    (event.keyCode === 65 && event.ctrlKey) ||
                    (event.keyCode === 67 && event.ctrlKey) ||
                    (event.keyCode === 86 && event.ctrlKey) ||
                    (event.keyCode === 88 && event.ctrlKey)) {
                    return;
                }
                if ((event.shiftKey || (event.keyCode < 48 || event.keyCode > 57)) && (event.keyCode < 96 || event.keyCode > 105)) {
                    event.preventDefault();
                }
            }
        };

        function initializeCurrencyInputs() {
            document.querySelectorAll('.currency-field').forEach(input => {
                input.addEventListener('input', function(e) {
                    const cursorPosition = e.target.selectionStart;
                    const oldValue = e.target.value;
                    const newValue = CurrencyUtils.formatCurrency(oldValue);
                    
                    if (newValue !== oldValue) {
                        e.target.value = newValue;
                        const newCursorPosition = cursorPosition + (newValue.length - oldValue.length);
                        e.target.setSelectionRange(newCursorPosition, newCursorPosition);
                    }
                });
                
                input.addEventListener('keydown', CurrencyUtils.validateNumericInput);
                input.addEventListener('blur', e => e.target.value = CurrencyUtils.formatCurrency(e.target.value));
            });
        }

        function autoResizeTextarea(textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize currency inputs
            initializeCurrencyInputs();
            
            // Initialize textarea auto-resize
            document.querySelectorAll('.auto-resize-textarea').forEach(textarea => {
                autoResizeTextarea(textarea);
                textarea.addEventListener('input', () => autoResizeTextarea(textarea));
                textarea.addEventListener('paste', () => setTimeout(() => autoResizeTextarea(textarea), 10));
            });
        });

        // Form submission with auto location capture
        document.getElementById('visitingForm').addEventListener('submit', async function(e) {
            e.preventDefault(); // Prevent default submission
            
            try {
                // Show loading overlay
                AutoLocationManager.showLoadingOverlay();
                
                // Get current location
                const location = await AutoLocationManager.getCurrentLocation();
                
                // Update hidden inputs
                document.getElementById('userLatitude').value = location.lat;
                document.getElementById('userLongitude').value = location.lon;
                
                // Get address
                const address = await AutoLocationManager.getAddressFromCoords(location.lat, location.lon);
                document.getElementById('userAddress').value = address;
                
                // Process currency fields before submission
                document.querySelectorAll('.currency-field').forEach(input => {
                    if (input.value) {
                        input.value = CurrencyUtils.removeCurrencyFormat(input.value);
                    }
                });
                
                // Hide loading overlay
                AutoLocationManager.hideLoadingOverlay();
                
                // Submit the form
                this.submit();
                
            } catch (error) {
                // Hide loading overlay
                AutoLocationManager.hideLoadingOverlay();
                
                // Show error message and ask if user wants to continue without location
                const continueWithoutLocation = confirm(
                    `Gagal mengambil lokasi: ${error.message}\n\n` +
                    'Apakah Anda ingin melanjutkan tanpa data lokasi?'
                );
                
                if (continueWithoutLocation) {
                    // Process currency fields before submission
                    document.querySelectorAll('.currency-field').forEach(input => {
                        if (input.value) {
                            input.value = CurrencyUtils.removeCurrencyFormat(input.value);
                        }
                    });
                    
                    // Submit the form without location
                    this.submit();
                }
            }
        });

        // Existing form logic for jenis kasus
        const JENIS_KASUS_MAPPING = {
            'Bacterial': 'bacterial', 'Viral': 'virus', 'Parasit': 'parasit',
            'Jamur': 'jamur', 'Lain-lain': 'lain_lain', 'Lambat puncak': null
        };

        const FormUtils = {
            isKasusSelected() {
                return Array.from(document.querySelectorAll('input[type="radio"]:checked')).some(radio => 
                    radio.closest('.mb-4').querySelector('label').textContent.toLowerCase().includes('tujuan kunjungan') &&
                    radio.value === 'Kasus'
                );
            },
            hideFormGroup(group) {
                group.classList.add('jenis-kasus-hidden');
                group.querySelectorAll('input, select, textarea').forEach(input => {
                    input.disabled = true;
                    input.removeAttribute('required');
                    if (input.type === 'radio' || input.type === 'checkbox') {
                        input.checked = false;
                    } else {
                        input.value = '';
                    }
                });
            },
            showFormGroup(group) {
                group.classList.remove('jenis-kasus-hidden');
                group.querySelectorAll('input, select, textarea').forEach(input => {
                    input.disabled = false;
                    const label = group.querySelector('label');
                    if (label && label.innerHTML.includes('<span class="required">*</span>')) {
                        input.setAttribute('required', 'required');
                    }
                });
            },
            findFormGroupByLabel(labelText) {
                return Array.from(document.querySelectorAll('.mb-4')).find(group => {
                    const label = group.querySelector('label');
                    return label && label.textContent.toLowerCase().includes(labelText.toLowerCase());
                });
            },
            hideAllJenisKasusFields() {
                const fieldTypes = ['bacterial', 'virus', 'parasit', 'jamur', 'lain-lain', 'lain_lain'];
                document.querySelectorAll('.mb-4').forEach(group => {
                    const label = group.querySelector('label');
                    if (label && fieldTypes.some(type => label.textContent.toLowerCase().includes(type))) {
                        this.hideFormGroup(group);
                    }
                });
            }
        };

        function toggleJenisKasus(tujuanKunjungan) {
            const jenisKasusGroup = FormUtils.findFormGroupByLabel('jenis kasus');
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
            
            FormUtils.hideAllJenisKasusFields();
            
            document.querySelectorAll('.mb-4').forEach(group => {
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

        document.addEventListener('DOMContentLoaded', function() {
            if (!FormUtils.isKasusSelected()) {
                const jenisKasusGroup = FormUtils.findFormGroupByLabel('jenis kasus');
                if (jenisKasusGroup) FormUtils.hideFormGroup(jenisKasusGroup);
                FormUtils.hideAllJenisKasusFields();
            }
        });
    </script>
</body>
</html>
