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
        
        /* Optimized loading overlay styles */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        .loading-content {
            background: white;
            padding: 25px 35px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            max-width: 300px;
        }
        
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #007bff;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .location-status {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            min-width: 200px;
        }

        .btn-submit {
            position: relative;
            overflow: hidden;
        }

        .btn-submit.loading {
            pointer-events: none;
        }

        .btn-submit .spinner-border {
            width: 1rem;
            height: 1rem;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Optimized Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-content">
            <div class="spinner"></div>
            <h6 class="mb-2">Mengambil Lokasi...</h6>
            <small class="text-muted">Pastikan GPS aktif</small>
        </div>
    </div>

    <!-- Location Status Alert -->
    <div id="locationStatus" class="location-status"></div>

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
                        <button type="submit" class="btn btn-primary px-4 btn-submit" id="submitBtn">
                            <span class="btn-text"><?php echo ($action_type === 'next') ? 'Next' : 'Submit'; ?></span>
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Optimized Auto Location Manager
        const AutoLocationManager = {
            locationCache: null,
            isGettingLocation: false,
            
            async getCurrentLocation() {
                // Return cached location if available and recent (within 5 minutes)
                if (this.locationCache && (Date.now() - this.locationCache.timestamp < 300000)) {
                    return { lat: this.locationCache.lat, lon: this.locationCache.lon };
                }

                if (this.isGettingLocation) {
                    throw new Error('Sedang mengambil lokasi, harap tunggu...');
                }

                this.isGettingLocation = true;

                try {
                    if (!navigator.geolocation) {
                        throw new Error('Geolocation tidak didukung oleh browser ini.');
                    }

                    // Show status
                    this.showLocationStatus('Mengambil lokasi GPS...', 'info');

                    const location = await new Promise((resolve, reject) => {
                        const timeoutId = setTimeout(() => {
                            reject(new Error('Timeout mengambil lokasi (8 detik)'));
                        }, 8000); // Reduced timeout to 8 seconds

                        navigator.geolocation.getCurrentPosition(
                            (position) => {
                                clearTimeout(timeoutId);
                                const lat = position.coords.latitude;
                                const lon = position.coords.longitude;
                                
                                // Cache the location
                                this.locationCache = {
                                    lat, lon, 
                                    timestamp: Date.now()
                                };
                                
                                resolve({ lat, lon });
                            },
                            (error) => {
                                clearTimeout(timeoutId);
                                let errorMessage = 'Gagal mengambil lokasi.';
                                
                                switch (error.code) {
                                    case error.PERMISSION_DENIED:
                                        errorMessage = 'Akses lokasi ditolak. Silakan izinkan akses lokasi.';
                                        break;
                                    case error.POSITION_UNAVAILABLE:
                                        errorMessage = 'Lokasi tidak tersedia. Pastikan GPS aktif.';
                                        break;
                                    case error.TIMEOUT:
                                        errorMessage = 'Timeout mengambil lokasi. Coba lagi.';
                                        break;
                                }
                                
                                reject(new Error(errorMessage));
                            },
                            {
                                enableHighAccuracy: true,
                                timeout: 7000, // Reduced timeout
                                maximumAge: 60000 // Cache for 1 minute
                            }
                        );
                    });

                    this.showLocationStatus('Lokasi berhasil diambil!', 'success');
                    return location;

                } finally {
                    this.isGettingLocation = false;
                }
            },

            async getAddressFromCoords(lat, lon) {
                try {
                    this.showLocationStatus('Mengambil alamat...', 'info');
                    
                    // Set timeout for address fetching
                    const controller = new AbortController();
                    const timeoutId = setTimeout(() => controller.abort(), 5000); // 5 second timeout

                    const response = await fetch(
                        `<?php echo base_url('location/reverse_geocode'); ?>?lat=${lat}&lon=${lon}`,
                        { 
                            signal: controller.signal,
                            method: 'GET',
                            cache: 'default'
                        }
                    );
                    
                    clearTimeout(timeoutId);
                    
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }
                    
                    const data = await response.json();
                    
                    if (data && data.display_name) {
                        this.showLocationStatus('Alamat berhasil diambil!', 'success');
                        return data.display_name;
                    }
                    
                    return `Koordinat: ${lat.toFixed(6)}, ${lon.toFixed(6)}`;
                    
                } catch (error) {
                    console.warn('Error getting address:', error);
                    // Return coordinates if address fetch fails
                    return `Koordinat: ${lat.toFixed(6)}, ${lon.toFixed(6)}`;
                }
            },

            showLoadingOverlay() {
                document.getElementById('loadingOverlay').style.display = 'flex';
            },

            hideLoadingOverlay() {
                document.getElementById('loadingOverlay').style.display = 'none';
            },

            showLocationStatus(message, type = 'info') {
                const statusDiv = document.getElementById('locationStatus');
                const alertClass = type === 'success' ? 'alert-success' : 
                                 type === 'error' ? 'alert-danger' : 'alert-info';
                
                statusDiv.innerHTML = `
                    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                        <small>${message}</small>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;

                // Auto hide success/info messages after 3 seconds
                if (type !== 'error') {
                    setTimeout(() => {
                        const alert = statusDiv.querySelector('.alert');
                        if (alert) {
                            const bsAlert = new bootstrap.Alert(alert);
                            bsAlert.close();
                        }
                    }, 3000);
                }
            },

            clearLocationStatus() {
                document.getElementById('locationStatus').innerHTML = '';
            }
        };

        // Currency utilities (unchanged)
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

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            initializeCurrencyInputs();
            
            document.querySelectorAll('.auto-resize-textarea').forEach(textarea => {
                autoResizeTextarea(textarea);
                textarea.addEventListener('input', () => autoResizeTextarea(textarea));
                textarea.addEventListener('paste', () => setTimeout(() => autoResizeTextarea(textarea), 10));
            });

            // Pre-fetch location when page loads for better UX
            if (navigator.geolocation) {
                setTimeout(() => {
                    AutoLocationManager.getCurrentLocation().catch(error => {
                        console.log('Background location fetch failed:', error.message);
                    });
                }, 1000);
            }
        });

        // Optimized form submission
        document.getElementById('visitingForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const btnText = submitBtn.querySelector('.btn-text');
            const spinner = submitBtn.querySelector('.spinner-border');
            
            // Show loading state on button
            submitBtn.classList.add('loading');
            btnText.textContent = 'Memproses...';
            spinner.classList.remove('d-none');
            
            try {
                AutoLocationManager.showLoadingOverlay();
                AutoLocationManager.clearLocationStatus();
                
                // Get current location (will use cache if available)
                const location = await AutoLocationManager.getCurrentLocation();
                
                // Update hidden inputs
                document.getElementById('userLatitude').value = location.lat;
                document.getElementById('userLongitude').value = location.lon;
                
                // Get address in parallel with form processing
                const addressPromise = AutoLocationManager.getAddressFromCoords(location.lat, location.lon);
                
                // Process currency fields
                document.querySelectorAll('.currency-field').forEach(input => {
                    if (input.value) {
                        input.value = CurrencyUtils.removeCurrencyFormat(input.value);
                    }
                });
                
                // Wait for address (with timeout)
                try {
                    const address = await Promise.race([
                        addressPromise,
                        new Promise((_, reject) => setTimeout(() => reject(new Error('Address timeout')), 3000))
                    ]);
                    document.getElementById('userAddress').value = address;
                } catch (addressError) {
                    // Continue without address if it fails
                    console.warn('Address fetch failed:', addressError.message);
                    document.getElementById('userAddress').value = `Koordinat: ${location.lat.toFixed(6)}, ${location.lon.toFixed(6)}`;
                }
                
                AutoLocationManager.hideLoadingOverlay();
                
                // Submit the form
                this.submit();
                
            } catch (error) {
                AutoLocationManager.hideLoadingOverlay();
                AutoLocationManager.showLocationStatus(`Gagal: ${error.message}`, 'error');
                
                // Reset button state
                submitBtn.classList.remove('loading');
                btnText.textContent = '<?php echo ($action_type === 'next') ? 'Next' : 'Submit'; ?>';
                spinner.classList.add('d-none');
                
                // Ask if user wants to continue without location
                const continueWithoutLocation = confirm(
                    `Gagal mengambil lokasi: ${error.message}\n\n` +
                    'Apakah Anda ingin melanjutkan tanpa data lokasi?'
                );
                
                if (continueWithoutLocation) {
                    // Show loading again
                    submitBtn.classList.add('loading');
                    btnText.textContent = 'Memproses...';
                    spinner.classList.remove('d-none');
                    
                    // Process currency fields
                    document.querySelectorAll('.currency-field').forEach(input => {
                        if (input.value) {
                            input.value = CurrencyUtils.removeCurrencyFormat(input.value);
                        }
                    });
                    
                    // Submit without location
                    this.submit();
                }
            }
        });

        // Existing form logic for jenis kasus (unchanged)
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
