<!DOCTYPE html>
<html>
<head>
    <title><?= isset($page_title) ? $page_title : 'Form Tambah Data Baru' ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-container { margin-left: 20px; }
        .page-title { margin-left: 10px; }
        .question-group { margin-bottom: 15px; }
        .dependent-field { display: none; }
        .custom-dropdown { position: relative; max-width: 400px; }
        .dropdown-toggle { 
            background: #fff; 
            color: #333; 
            border: 1px solid #dee2e6; 
            cursor: pointer; 
            text-align: left; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
        }
        .dropdown-toggle:hover, .dropdown-toggle:focus { 
            background: #f8f9fa; 
            border-color: #0d6efd; 
        }
        .dropdown-toggle::after { 
            content: "▼"; 
            font-size: 12px; 
        }
        .dropdown-content { 
            display: none; 
            position: absolute; 
            background: #fff; 
            min-width: 100%; 
            max-height: 200px; 
            overflow-y: auto; 
            border: 1px solid #dee2e6; 
            border-radius: 0 0 .375rem .375rem; 
            border-top: none; 
            z-index: 1000; 
            box-shadow: 0 .125rem .25rem rgba(0,0,0,.075);
        }
        .dropdown-content .dropdown-option { 
            color: #333; 
            padding: 10px 12px; 
            text-decoration: none; 
            display: block; 
            cursor: pointer; 
            border-bottom: 1px solid #eee;
        }
        .dropdown-content .dropdown-option:hover { 
            background: #f8f9fa; 
        }
        .show { display: block; }
        .selected-item { background: #fff; border-color: #dee2e6; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <h2 class="page-title mb-4"><?= isset($page_title) ? $page_title : 'Form Tambah Data Baru' ?></h2>

        <form method="post" action="" class="form-container" id="mainForm">
            <?php if (!empty($questions_kategori)): ?>
                <?php foreach ($questions_kategori as $q): ?>
                    <div class="question-group <?= (in_array($q['field_name'], ['agen_dari', 'sub_agen_dari', 'kemitraan_dari'])) ? 'dependent-field' : '' ?>" 
                         id="field-<?= $q['field_name'] ?>"
                         <?= ($q['field_name'] == 'jenis_peternak') ? 'data-jenis-peternak="true"' : '' ?>>
                        <label class="form-label fw-bold mb-1">
                            <?= $q['question_text'] ?>
                            <?php if (!empty($q['required'])): ?>
                                <span class="text-danger">*</span>
                            <?php endif; ?>
                        </label>
                        
                        <?php if ($q['type'] == 'number' || $q['field_name'] == 'kapasitas_peternak' || $q['field_name'] == 'jumlah_kandang_peternak' || $q['field_name'] == 'kapasitas_farm'): ?>
                            <input type="text"
                                   inputmode="numeric" 
                                   class="form-control mt-1 numeric-input"
                                   style="max-width: 400px"
                                   name="q<?= $q['questions_id'] ?>" 
                                   placeholder="Masukkan angka"
                                   <?= !empty($q['required']) ? 'required' : '' ?>>

                        <?php elseif ($q['type'] == 'text'): ?>
                            <input type="text" 
                                   name="q<?= $q['questions_id'] ?>" 
                                   class="form-control mt-1"
                                   style="max-width: 400px"
                                   placeholder="Masukkan jawaban"
                                   <?= !empty($q['required']) ? 'required' : '' ?>>
                                    
                        <?php elseif ($q['type'] == 'date'): ?>
                            <input type="date" 
                                   name="q<?= $q['questions_id'] ?>" 
                                   class="form-control mt-1"
                                   style="max-width: 400px"
                                   <?= !empty($q['required']) ? 'required' : '' ?>>

                        <?php elseif ($q['type'] == 'radio' && !empty($q['options'])): ?>
                            <div class="mt-1">
                                <?php foreach ($q['options'] as $opt): ?>
                                    <div class="form-check my-1">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="q<?= $q['questions_id'] ?>" 
                                               value="<?= $opt['option_text'] ?>"
                                               id="r_<?= $q['questions_id'] ?>_<?= $opt['options_id'] ?? rand() ?>"
                                               <?= !empty($q['required']) ? 'required' : '' ?>>
                                        <label class="form-check-label" 
                                               for="r_<?= $q['questions_id'] ?>_<?= $opt['options_id'] ?? rand() ?>">
                                            <?= $opt['option_text'] ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        <?php elseif ($q['type'] == 'select' && !empty($q['options'])): ?>
                            <?php if ($q['field_name'] == 'jenis_peternak'): ?>
                                <select name="q<?= $q['questions_id'] ?>" 
                                        class="form-select mt-1"
                                        style="max-width: 400px"
                                        <?= !empty($q['required']) ? 'required' : '' ?>
                                        onchange="toggleDependentFields(this.value)">
                                    <option value="">-- Pilih Jawaban --</option>
                                    <?php foreach ($q['options'] as $opt): ?>
                                        <option value="<?= $opt['option_text'] ?>">
                                            <?= $opt['option_text'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <select name="q<?= $q['questions_id'] ?>" 
                                        class="form-select mt-1"
                                        style="max-width: 400px"
                                        <?= !empty($q['required']) ? 'required' : '' ?>>
                                    <option value="">-- Pilih Jawaban --</option>
                                    <?php foreach ($q['options'] as $opt): ?>
                                        <option value="<?= $opt['option_text'] ?>">
                                            <?= $opt['option_text'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>

                        <?php elseif ($q['type'] == 'checkbox' && !empty($q['options'])): ?>
                            <div class="mt-1">
                                <?php foreach ($q['options'] as $opt): ?>
                                    <div class="form-check my-1">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="q<?= $q['questions_id'] ?>[]" 
                                               value="<?= $opt['option_text'] ?>"
                                               id="c_<?= $q['questions_id'] ?>_<?= $opt['options_id'] ?? rand() ?>">
                                        <label class="form-check-label" 
                                               for="c_<?= $q['questions_id'] ?>_<?= $opt['options_id'] ?? rand() ?>">
                                            <?= $opt['option_text'] ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        <?php elseif ($q['type'] == 'textarea'): ?>
                            <textarea name="q<?= $q['questions_id'] ?>"
                                      class="form-control mt-1"
                                      rows="4" 
                                      style="max-width: 400px"
                                      placeholder="Masukkan jawaban"
                                      <?= !empty($q['required']) ? 'required' : '' ?>></textarea>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <button type="submit" name="submit_form" value="1" class="btn btn-primary px-4 py-2 mt-4">Submit</button>
            <?php else: ?>
                <div class="alert alert-info">
                    <p class="mb-0 fst-italic">Tidak ada pertanyaan yang tersedia untuk kategori ini.</p>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
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
            // Logic for dependent fields
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

                // Jika setelah dibersihkan tidak ada angka, kosongkan input dan berhenti
                if (value.trim() === '') {
                    input.value = '';
                    return;
                }
                
                const lengthBeforeFormatting = input.value.length;
                let formattedValue = new Intl.NumberFormat('en-US').format(value);
                input.value = formattedValue;
                const lengthAfterFormatting = input.value.length;

                // Menyesuaikan posisi kursor
                cursorPosition += (lengthAfterFormatting - lengthBeforeFormatting);
                input.setSelectionRange(cursorPosition, cursorPosition);
            }

            document.querySelectorAll('.numeric-input').forEach(input => {
                input.addEventListener('input', formatNumber);
                // Format awal jika field sudah ada nilainya saat halaman dimuat
                if (input.value) {
                    formatNumber({ target: input });
                }
            });
        });
    </script>
</body>
</html>
