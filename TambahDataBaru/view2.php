<!DOCTYPE html>
<html>
<head>
    <title><?= isset($page_title) ? $page_title : 'Form Tambah Data Baru' ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-container { 
            margin-left: 20px; 
        }
        
        .page-title { 
            margin-left: 10px; 
        }
        
        .form-group { 
            margin-bottom: 20px; 
        }
        
        .options-group { 
            margin: 5px 0; 
        }
        
        .dependent-field { 
            display: none; 
        }
        
        .auto-resize-textarea { 
            resize: none;
            min-height: 80px;
            overflow: hidden;
            line-height: 1.4;
        }
        
        /* Custom dropdown styles for dependent fields */
        .custom-dropdown { 
            position: relative; 
        }
        
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
        
        .dropdown-toggle::after {
            content: "▼";
            font-size: 12px;
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
        
        .dropdown-content .option-item {
            color: #333;
            padding: 10px 12px;
            text-decoration: none;
            display: block;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        
        .dropdown-content .option-item:hover { 
            background-color: #f8f9fa; 
        }
        
        .dropdown-content .option-item:last-child { 
            border-bottom: none; 
        }
        
        .show { 
            display: block; 
        }
        
        .selected-option { 
            background-color: #fefefeff; 
            border-color: #dee2e6; 
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <h2 class="page-title mb-4"><?= isset($page_title) ? $page_title : 'Form Tambah Data Baru' ?></h2>

        <form method="post" action="" id="masterForm" class="form-container">
            <?php if (!empty($questions_kategori)): ?>
                <?php foreach ($questions_kategori as $q): ?>
                    <div class="form-group mb-4 <?= (in_array($q['field_name'], ['agen_dari', 'sub_agen_dari', 'kemitraan_dari'])) ? 'dependent-field' : '' ?>" 
                         id="field-<?= $q['field_name'] ?>"
                         data-field="<?= $q['field_name'] ?>"
                         <?= ($q['field_name'] == 'jenis_peternak') ? 'data-jenis-peternak="true"' : '' ?>>
                        
                        <label class="form-label fw-bold">
                            <?= $q['question_text'] ?>
                            <?php if (!empty($q['required'])): ?>
                                <span class="text-danger">*</span>
                            <?php endif; ?>
                        </label>

                        <?php if ($q['type'] == 'number' || $q['field_name'] == 'kapasitas_peternak' || $q['field_name'] == 'jumlah_kandang_peternak' || $q['field_name'] == 'kapasitas_farm'): ?>
                            <input type="text"
                                   inputmode="numeric" 
                                   class="form-control numeric-input"
                                   name="q<?= $q['questions_id'] ?>" 
                                   placeholder="Masukkan angka"
                                   style="max-width: 400px;"
                                   <?= !empty($q['required']) ? 'required' : '' ?>>

                        <?php elseif ($q['type'] == 'text'): ?>
                            <?php 
                                // MODIFIED: Add a special class and inputmode for phone number fields
                                $extra_class = '';
                                $extra_attr = '';
                                if (strpos($q['field_name'], 'nomor_telepon') !== false) {
                                    $extra_class = ' phone-input';
                                    $extra_attr = 'inputmode="tel"';
                                }
                            ?>
                            <input type="text" 
                                   name="q<?= $q['questions_id'] ?>" 
                                   class="form-control<?= $extra_class ?>"
                                   placeholder="Masukkan jawaban Anda"
                                   style="max-width: 400px;"
                                   <?= $extra_attr ?>
                                   <?= !empty($q['required']) ? 'required' : '' ?>>
                                   
                        <?php elseif ($q['type'] == 'date'): ?>
                            <input type="date" 
                                   name="q<?= $q['questions_id'] ?>" 
                                   class="form-control"
                                   style="max-width: 400px;"
                                   <?= !empty($q['required']) ? 'required' : '' ?>>

                        <?php elseif ($q['type'] == 'radio' && !empty($q['options'])): ?>
                            <div class="mt-2">
                                <?php foreach ($q['options'] as $opt): ?>
                                    <div class="form-check options-group">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="q<?= $q['questions_id'] ?>" 
                                               value="<?= $opt['option_text'] ?>"
                                               id="radio_<?= $q['questions_id'] ?>_<?= $opt['options_id'] ?? rand() ?>"
                                               <?= !empty($q['required']) ? 'required' : '' ?>>
                                        <label class="form-check-label" for="radio_<?= $q['questions_id'] ?>_<?= $opt['options_id'] ?? rand() ?>">
                                            <?= $opt['option_text'] ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        <?php elseif ($q['type'] == 'select' && !empty($q['options'])): ?>
                            <select name="q<?= $q['questions_id'] ?>" 
                                    class="form-select"
                                    style="max-width: 400px;"
                                    <?= !empty($q['required']) ? 'required' : '' ?>
                                    <?= ($q['field_name'] == 'jenis_peternak') ? 'onchange="toggleDependentFields(this.value)"' : '' ?>>
                                <option value="">-- Pilih Jawaban --</option>
                                <?php foreach ($q['options'] as $opt): ?>
                                    <option value="<?= $opt['option_text'] ?>">
                                        <?= $opt['option_text'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                        <?php elseif ($q['type'] == 'checkbox' && !empty($q['options'])): ?>
                            <div class="mt-2">
                                <?php foreach ($q['options'] as $opt): ?>
                                    <div class="form-check options-group">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="q<?= $q['questions_id'] ?>[]" 
                                               value="<?= $opt['option_text'] ?>"
                                               id="checkbox_<?= $q['questions_id'] ?>_<?= $opt['options_id'] ?? rand() ?>">
                                        <label class="form-check-label" for="checkbox_<?= $q['questions_id'] ?>_<?= $opt['options_id'] ?? rand() ?>">
                                            <?= $opt['option_text'] ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        <?php elseif ($q['type'] == 'textarea'): ?>
                            <textarea name="q<?= $q['questions_id'] ?>"
                                      class="form-control auto-resize-textarea"
                                      placeholder="Masukkan jawaban Anda"
                                      style="max-width: 400px;"
                                      <?= !empty($q['required']) ? 'required' : '' ?>></textarea>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                
                <button type="submit" name="submit_form" class="btn btn-primary px-4 py-2 mt-4">
                    Submit
                </button>
                
            <?php else: ?>
                <div class="alert alert-info">
                    <p class="mb-0 fst-italic">Tidak ada pertanyaan yang tersedia untuk kategori ini.</p>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-resize textarea function
        function autoResizeTextarea(textarea) {
            textarea.style.height = 'auto';
            const computed = window.getComputedStyle(textarea);
            const minHeight = parseInt(computed.minHeight);
            const padding = parseInt(computed.paddingTop) + parseInt(computed.paddingBottom);
            const border = parseInt(computed.borderTopWidth) + parseInt(computed.borderBottomWidth);
            const newHeight = Math.max(minHeight, textarea.scrollHeight + border);
            textarea.style.height = newHeight + 'px';
        }

        // Initialize auto-resize for all textareas
        function initializeAutoResize() {
            const textareas = document.querySelectorAll('.auto-resize-textarea');
            textareas.forEach(textarea => {
                autoResizeTextarea(textarea);
                textarea.addEventListener('input', function() { autoResizeTextarea(this); });
                textarea.addEventListener('paste', function() { setTimeout(() => autoResizeTextarea(this), 10); });
                textarea.addEventListener('focus', function() { autoResizeTextarea(this); });
            });
        }

        function toggleDependentFields(selectedValue) {
            const dependentFields = document.querySelectorAll('.dependent-field');
            dependentFields.forEach(field => {
                field.style.display = 'none';
                const inputs = field.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    input.removeAttribute('required');
                });
            });

            if (selectedValue === 'Agen') {
                const agenField = document.getElementById('field-agen_dari');
                if (agenField) {
                    agenField.style.display = 'block';
                    const qData = <?= json_encode($questions_kategori) ?>.find(q => q.field_name === 'agen_dari');
                    if (qData && qData.required) {
                        const inputs = agenField.querySelectorAll('input, select, textarea');
                        inputs.forEach(input => {
                            input.setAttribute('required', 'required');
                        });
                    }
                }
            } else if (selectedValue === 'Sub Agen') {
                const subAgenField = document.getElementById('field-sub_agen_dari');
                if (subAgenField) {
                    subAgenField.style.display = 'block';
                    const qData = <?= json_encode($questions_kategori) ?>.find(q => q.field_name === 'sub_agen_dari');
                    if (qData && qData.required) {
                        const inputs = subAgenField.querySelectorAll('input, select, textarea');
                        inputs.forEach(input => {
                            input.setAttribute('required', 'required');
                        });
                    }
                }
            } else if (selectedValue === 'Kemitraan') {
                const kemitraanField = document.getElementById('field-kemitraan_dari');
                if (kemitraanField) {
                    kemitraanField.style.display = 'block';
                    const qData = <?= json_encode($questions_kategori) ?>.find(q => q.field_name === 'kemitraan_dari');
                    if (qData && qData.required) {
                        const inputs = kemitraanField.querySelectorAll('input, select, textarea');
                        inputs.forEach(input => {
                            input.setAttribute('required', 'required');
                        });
                    }
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            initializeAutoResize();
            
            const jenisPeternakGroup = document.querySelector('[data-jenis-peternak="true"]');
            if (jenisPeternakGroup) {
                const jenisPeternakSelect = jenisPeternakGroup.querySelector('select');
                if (jenisPeternakSelect) {
                    toggleDependentFields(jenisPeternakSelect.value);
                }
            }

            function formatNumber(e) {
                let input = e.target;
                let value = input.value.replace(/[^\d]/g, ''); 
                let cursorPosition = input.selectionStart;

                if (value.trim() === '') {
                    input.value = '';
                    return;
                }
                
                const lengthBeforeFormatting = input.value.length;
                let formattedValue = new Intl.NumberFormat('en-US').format(value);
                input.value = formattedValue;
                const lengthAfterFormatting = input.value.length;

                cursorPosition += (lengthAfterFormatting - lengthBeforeFormatting);
                input.setSelectionRange(cursorPosition, cursorPosition);
            }

            document.querySelectorAll('.numeric-input').forEach(input => {
                input.addEventListener('input', formatNumber);
                if (input.value) {
                    formatNumber({ target: input });
                }
            });
            
            // ADDED: Logic to restrict phone number fields to digits only
            document.querySelectorAll('.phone-input').forEach(input => {
                input.addEventListener('input', function (e) {
                    // Replace any character that is not a digit with an empty string
                    e.target.value = e.target.value.replace(/[^\d]/g, '');
                });
            });

            document.getElementById('masterForm').addEventListener('submit', function(e) {
                const numericInputs = document.querySelectorAll('.numeric-input');
                numericInputs.forEach(function(input) {
                    input.value = input.value.replace(/,/g, '');
                });
            });
        });

        window.addEventListener('resize', function() {
            const textareas = document.querySelectorAll('.auto-resize-textarea');
            textareas.forEach(textarea => {
                autoResizeTextarea(textarea);
            });
        });
    </script>
</body>
</html>
