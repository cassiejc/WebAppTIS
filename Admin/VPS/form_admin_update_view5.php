<!DOCTYPE html>
<html>
<head>
    <title><?= isset($page_title) ? $page_title : 'Form Edit Data' ?></title>
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
            content: "â–¼"; 
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
        .form-label { font-weight: 600; }
        .btn-secondary { margin-right: 10px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <h2 class="page-title mb-4"><?= isset($page_title) ? $page_title : 'Form Edit Data' ?></h2>
	<?php
	// TAMBAHKAN BLOK INI UNTUK MENAMPILKAN PESAN ERROR/SUKSES
	if ($this->session->flashdata('error')) {
 	   echo '<div class="alert alert-danger">' . $this->session->flashdata('error') . '</div>';
	}
	if ($this->session->flashdata('success')) {
  	  echo '<div class="alert alert-success">' . $this->session->flashdata('success') . '</div>';
	}
	?>

        <?php
            $action_url = isset($form_action) ? $form_action : ''; 
            $attributes = ['class' => 'form-container', 'id' => 'mainForm'];
            echo form_open($action_url, $attributes);
        ?>

        <?php if (isset($edit_id) && !empty($edit_id)): ?>
            <input type="hidden" name="edit_id" value="<?= htmlspecialchars($edit_id) ?>">
        <?php endif; ?>    
        
        <?php if (!empty($questions_kategori)): ?>
                <?php foreach ($questions_kategori as $q): ?>
                    <?php
                    // Get current value from existing data
                    $current_value = '';
                    if (isset($existing_data[$q['field_name']])) {
                        $current_value = $existing_data[$q['field_name']];
                    }
                    
                    // Special handling for jenis_peternak field to extract the main type
                    $jenis_peternak_value = '';
                    $selected_dari_value = '';
                    if ($q['field_name'] == 'jenis_peternak' && !empty($current_value)) {
                        if (strpos($current_value, ':') !== false) {
                            $parts = explode(':', $current_value, 2);
                            $jenis_peternak_value = trim($parts[0]);
                            $selected_dari_value = trim($parts[1]);
                        } else {
                            $jenis_peternak_value = $current_value;
                        }
                        $current_value = $jenis_peternak_value;
                    }
                    
                    // For dependent fields, get the selected value
                    if (in_array($q['field_name'], ['agen_dari', 'sub_agen_dari', 'kemitraan_dari'])) {
                        $current_value = $selected_dari_value;
                    }
                    ?>
                    
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
                                value="<?= htmlspecialchars($current_value) ?>"
                                placeholder="Masukkan angka"
                                <?= !empty($q['required']) ? 'required' : '' ?>>

                        <?php elseif ($q['type'] == 'text_readonly'): ?>
                            <input type="text" 
           			name="q<?= $q['questions_id'] ?>" 
                                class="form-control mt-1"
                                style="max-width: 400px"
                                value="<?= htmlspecialchars($current_value) ?>"
                                readonly>
                            <div class="form-text">Data ini tidak dapat diubah.</div>

                        <?php elseif ($q['type'] == 'text'): ?>
                            <input type="text" 
                                name="q<?= $q['questions_id'] ?>" 
                                class="form-control mt-1"
                                style="max-width: 400px"
                                value="<?= htmlspecialchars($current_value) ?>"
                                placeholder="Masukkan jawaban"
                                <?= !empty($q['required']) ? 'required' : '' ?>>
                                
                        <?php elseif ($q['type'] == 'date'): ?>
                            <input type="date" 
                                name="q<?= $q['questions_id'] ?>" 
                                class="form-control mt-1"
                                style="max-width: 400px"
                                value="<?= htmlspecialchars($current_value) ?>"
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
                                            <?= ($current_value == $opt['option_text']) ? 'checked' : '' ?>
                                            <?= !empty($q['required']) ? 'required' : '' ?>>
                                        <label class="form-check-label" 
                                            for="r_<?= $q['questions_id'] ?>_<?= $opt['options_id'] ?? rand() ?>">
                                            <?= $opt['option_text'] ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        <?php elseif ($q['type'] == 'select'): ?>
                            <?php 
                                $onchange_attr = ($q['field_name'] == 'jenis_peternak') ? 'onchange="toggleDependentFields(this.value)"' : '';
                            ?>
                            <select name="q<?= $q['questions_id'] ?>" 
                                    class="form-select mt-1"
                                    style="max-width: 400px"
                                    <?= !empty($q['required']) ? 'required' : '' ?>
                                    <?= $onchange_attr ?>>
                                <option value="">-- Pilih Jawaban --</option>
                                <?php if (!empty($q['options'])): ?>
                                    <?php foreach ($q['options'] as $opt): ?>
                                        <?php
                                            $option_value = isset($opt['option_value']) ? $opt['option_value'] : $opt['option_text'];
                                            $option_text = $opt['option_text'];
                                        ?>
                                        <option value="<?= htmlspecialchars($option_value) ?>" <?= ($current_value == $option_value) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($option_text) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>Tidak ada opsi tersedia</option>
                                <?php endif; ?>
                            </select>

                        <?php elseif ($q['type'] == 'checkbox' && !empty($q['options'])): ?>
                            <?php 
                            $selected_checkboxes = [];
                            if (!empty($current_value)) {
                                $selected_checkboxes = is_array($current_value) ? $current_value : explode(',', $current_value);
                            }
                            ?>
                            <div class="mt-1">
                                <?php foreach ($q['options'] as $opt): ?>
                                    <div class="form-check my-1">
                                        <input class="form-check-input" 
                                            type="checkbox" 
                                            name="q<?= $q['questions_id'] ?>[]" 
                                            value="<?= $opt['option_text'] ?>"
                                            id="c_<?= $q['questions_id'] ?>_<?= $opt['options_id'] ?? rand() ?>"
                                            <?= in_array($opt['option_text'], $selected_checkboxes) ? 'checked' : '' ?>>
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
                                    <?= !empty($q['required']) ? 'required' : '' ?>><?= htmlspecialchars($current_value) ?></textarea>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                
                <div class="mt-4">
                    <a href="<?= site_url('Dashboard_new/index') ?>" class="btn btn-secondary px-4 py-2 mb-2">Batal</a>
                    <button type="submit" name="submit_form" value="1" class="btn btn-primary px-4 py-2 mb-2">Update</button>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <p class="mb-0 fst-italic">Tidak ada pertanyaan yang tersedia untuk kategori ini.</p>
                </div>
                <div class="mt-4">
                    <a href="<?= site_url('Dashboard_new/index') ?>" class="btn btn-secondary px-4 py-2">Kembali</a>
                </div>
            <?php endif; ?>
        <?php echo form_close(); ?>
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
            // Logic for dependent fields - initialize on page load
            const jenisPeternakGroup = document.querySelector('[data-jenis-peternak="true"]');
            if (jenisPeternakGroup) {
                const jenisPeternakSelect = jenisPeternakGroup.querySelector('select');
                if (jenisPeternakSelect) {
                    toggleDependentFields(jenisPeternakSelect.value);
                }
            }

            function formatNumber(e) {
                let input = e.target;
                let value = input.value.replace(/\D/g, ''); // Only remove commas, keep other digits
                
                // Store cursor position
                let cursorPosition = input.selectionStart;
                
                // If empty after cleaning, reset input
                if (value.trim() === '') {
                    input.value = '';
                    return;
                }
                
                const lengthBeforeFormatting = input.value.length;
                let formattedValue = new Intl.NumberFormat('en-US').format(value);
                input.value = formattedValue;
                const lengthAfterFormatting = input.value.length;

                // Adjust cursor position
                cursorPosition += (lengthAfterFormatting - lengthBeforeFormatting);
                input.setSelectionRange(cursorPosition, cursorPosition);
            }

            document.querySelectorAll('.numeric-input').forEach(input => {
                input.addEventListener('input', formatNumber);
                // Format existing values on page load
                if (input.value && input.value.trim() !== '') {
                    // Format the initial value if it's numeric
                    let initialValue = input.value.replace(/,/g, '');
                    if (initialValue && !isNaN(initialValue)) {
                        input.value = new Intl.NumberFormat('en-US').format(initialValue);
                    }
                }
            });

            // Add form submit handler
            document.getElementById('mainForm').addEventListener('submit', function(e) {
                document.querySelectorAll('.numeric-input').forEach(input => {
                    // Only remove commas before submitting, keep all other digits
                    input.value = input.value.replace(/,/g, '');
                });
            });
        });
    </script>
</body>
</html>
