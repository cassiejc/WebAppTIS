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
        .location-section {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .location-info {
            background-color: #fff;
            border-radius: 5px;
            padding: 15px;
            border: 1px solid #e0e0e0;
        }
        .location-status {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .location-status.success { color: #28a745; }
        .location-status.error { color: #dc3545; }
        .location-status.loading { color: #007bff; }
        #getLocationBtn {
            background-color: #28a745;
            border-color: #28a745;
        }
        #getLocationBtn:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        #getLocationBtn:disabled {
            background-color: #6c757d;
            border-color: #6c757d;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12 col-lg-8">
                <h2 class="mb-4 text-dark fw-bold">Visiting <?php echo $visiting_type; ?> - <?php echo $current_sub_area['nama_sub_area']; ?></h2>

                <!-- Location Section -->
                <div class="location-section">
                    <h5 class="mb-3">📍 Lokasi Kunjungan</h5>
                    <button type="button" id="getLocationBtn" class="btn btn-success">
                        <i class="fas fa-map-marker-alt"></i> Ambil Lokasi Saat Ini
                    </button>
                    <div id="locationInfo" class="location-info mt-3" style="display: none;">
                        <div id="locationStatus" class="location-status"></div>
                        <div id="locationDetails"></div>
                    </div>
                </div>

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
        // Location functionality
        const LocationManager = {
            isLocationCaptured: false,
            
            init() {
                document.getElementById('getLocationBtn').addEventListener('click', () => {
                    this.getCurrentLocation();
                });
            },

            getCurrentLocation() {
                const btn = document.getElementById('getLocationBtn');
                const locationInfo = document.getElementById('locationInfo');
                const locationStatus = document.getElementById('locationStatus');
                const locationDetails = document.getElementById('locationDetails');

                if (!navigator.geolocation) {
                    this.showError('Geolocation tidak didukung oleh browser ini.');
                    return;
                }

                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengambil Lokasi...';
                
                locationInfo.style.display = 'block';
                locationStatus.className = 'location-status loading';
                locationStatus.textContent = 'Sedang mengambil lokasi...';
                locationDetails.innerHTML = '';

                navigator.geolocation.getCurrentPosition(
                    (position) => this.onLocationSuccess(position),
                    (error) => this.onLocationError(error),
                    {
                        enableHighAccuracy: true,
                        timeout: 15000,
                        maximumAge: 0
                    }
                );
            },

            onLocationSuccess(position) {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                const accuracy = position.coords.accuracy;

                // Update hidden inputs
                document.getElementById('userLatitude').value = lat;
                document.getElementById('userLongitude').value = lon;

                // Update UI
                const locationStatus = document.getElementById('locationStatus');
                const locationDetails = document.getElementById('locationDetails');

                locationStatus.className = 'location-status success';
                locationStatus.textContent = '✅ Lokasi berhasil diperoleh!';

                locationDetails.innerHTML = `
                    <div><strong>Latitude:</strong> ${lat.toFixed(6)}</div>
                    <div><strong>Longitude:</strong> ${lon.toFixed(6)}</div>
                    <div><strong>Akurasi:</strong> ±${Math.round(accuracy)} meter</div>
                    <div class="mt-2"><small class="text-muted">Mengambil alamat...</small></div>
                `;

                // Get address via reverse geocoding
                this.getAddressFromCoords(lat, lon);
                
                this.isLocationCaptured = true;
                this.resetButton(true);
            },

            async getAddressFromCoords(lat, lon) {
                try {
                    const response = await fetch(`<?php echo base_url('location/reverse_geocode'); ?>?lat=${lat}&lon=${lon}`);
                    const data = await response.json();
                    
                    let address = 'Alamat tidak ditemukan';
                    if (data && data.display_name) {
                        address = data.display_name;
                        document.getElementById('userAddress').value = address;
                    }

                    // Update address in UI
                    const locationDetails = document.getElementById('locationDetails');
                    const currentContent = locationDetails.innerHTML;
                    locationDetails.innerHTML = currentContent.replace(
                        '<div class="mt-2"><small class="text-muted">Mengambil alamat...</small></div>',
                        `<div class="mt-2"><strong>Alamat:</strong> <small>${address}</small></div>`
                    );

                } catch (error) {
                    console.error('Error getting address:', error);
                    const locationDetails = document.getElementById('locationDetails');
                    const currentContent = locationDetails.innerHTML;
                    locationDetails.innerHTML = currentContent.replace(
                        '<div class="mt-2"><small class="text-muted">Mengambil alamat...</small></div>',
                        '<div class="mt-2"><small class="text-danger">Gagal mengambil alamat</small></div>'
                    );
                }
            },

            onLocationError(error) {
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

                this.showError(errorMessage);
                this.resetButton(false);
            },

            showError(message) {
                const locationInfo = document.getElementById('locationInfo');
                const locationStatus = document.getElementById('locationStatus');
                const locationDetails = document.getElementById('locationDetails');

                locationInfo.style.display = 'block';
                locationStatus.className = 'location-status error';
                locationStatus.textContent = '❌ Gagal mengambil lokasi';
                locationDetails.innerHTML = `<div class="text-danger">${message}</div>`;
            },

            resetButton(success) {
                const btn = document.getElementById('getLocationBtn');
                btn.disabled = false;
                
                if (success) {
                    btn.innerHTML = '<i class="fas fa-check"></i> Lokasi Sudah Diperoleh';
                    btn.className = 'btn btn-success';
                } else {
                    btn.innerHTML = '<i class="fas fa-map-marker-alt"></i> Coba Ambil Lokasi Lagi';
                    btn.className = 'btn btn-warning';
                }
            }
        };

        // Currency utilities (existing code)
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
            // Initialize location manager
            LocationManager.init();
            
            // Initialize currency inputs
            initializeCurrencyInputs();
            
            // Initialize textarea auto-resize
            document.querySelectorAll('.auto-resize-textarea').forEach(textarea => {
                autoResizeTextarea(textarea);
                textarea.addEventListener('input', () => autoResizeTextarea(textarea));
                textarea.addEventListener('paste', () => setTimeout(() => autoResizeTextarea(textarea), 10));
            });
        });

        // Form submission with location validation
        document.getElementById('visitingForm').addEventListener('submit', function(e) {
            // Check if location is required (you can modify this logic)
            const requireLocation = true; // Set to false if location is optional
            
            if (requireLocation && !LocationManager.isLocationCaptured) {
                e.preventDefault();
                alert('Mohon ambil lokasi terlebih dahulu sebelum submit form!');
                document.getElementById('getLocationBtn').focus();
                return false;
            }

            // Process currency fields before submission
            document.querySelectorAll('.currency-field').forEach(input => {
                if (input.value) {
                    input.value = CurrencyUtils.removeCurrencyFormat(input.value);
                }
            });
        });

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
