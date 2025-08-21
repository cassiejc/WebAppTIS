<!DOCTYPE html>
<html>
<head>
    <title>Form Tambah Data Baru</title>
    <style>
        form { margin-left: 20px; }
        h2 { margin-left: 10px; }
        .form-group { margin-bottom: 20px; }
        .options-group { margin: 5px 0; }
        .dependent-field { display: none; }
    </style>
</head>
<body>
    <h2>Form Tambah Data Baru</h2>

    <form method="post" action="">
        <!-- Category Selection Form -->
        <?php if (!empty($questions)): ?>
            <?php foreach ($questions as $q): ?>
                <div class="form-group">
                    <label>
                        <?= $q['question_text'] ?>
                        <?php if (!empty($q['required'])): ?>
                            <span style="color: red">*</span>
                        <?php endif; ?>
                    </label>
                    <br>
                    <?php if (!empty($q['options'])): ?>
                        <select name="kategori_tambah" 
                                onchange="this.form.submit()"
                                <?= !empty($q['required']) ? 'required' : '' ?>>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($q['options'] as $opt): ?>
                                <option value="<?= $opt['option_text'] ?>"
                                        <?= ($kategori_selected == $opt['option_text']) ? 'selected' : '' ?>>
                                    <?= $opt['option_text'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Category Specific Questions -->
        <?php if (!empty($questions_kategori)): ?>
            <h3>Form <?= $kategori_selected ?></h3>
            <?php foreach ($questions_kategori as $q): ?>
                <div class="form-group <?= (in_array($q['field_name'], ['agen_dari', 'sub_agen_dari', 'kemitraan_dari'])) ? 'dependent-field' : '' ?>" 
                     id="field-<?= $q['field_name'] ?>"
                     <?= ($q['field_name'] == 'jenis_peternak') ? 'data-jenis-peternak="true"' : '' ?>>
                    <label>
                        <?= $q['question_text'] ?>
                        <?php if (!empty($q['required'])): ?>
                            <span style="color: red">*</span>
                        <?php endif; ?>
                    </label>
                    <br>
                    <?php if ($q['type'] == 'text'): ?>
                        <input type="text" 
                               name="q<?= $q['questions_id'] ?>" 
                               <?= !empty($q['required']) ? 'required' : '' ?>>

                    <?php elseif ($q['type'] == 'number'): ?>
                        <input type="number" 
                               name="q<?= $q['questions_id'] ?>" 
                               <?= !empty($q['required']) ? 'required' : '' ?>>

                    <?php elseif ($q['type'] == 'date'): ?>
                        <input type="date" 
                               name="q<?= $q['questions_id'] ?>" 
                               <?= !empty($q['required']) ? 'required' : '' ?>>

                    <?php elseif ($q['type'] == 'radio' && !empty($q['options'])): ?>
                        <?php foreach ($q['options'] as $opt): ?>
                            <div class="options-group">
                                <input type="radio" 
                                       name="q<?= $q['questions_id'] ?>" 
                                       value="<?= $opt['option_text'] ?>"
                                       <?= !empty($q['required']) ? 'required' : '' ?>>
                                <?= $opt['option_text'] ?>
                            </div>
                        <?php endforeach; ?>

                    <?php elseif ($q['type'] == 'select' && !empty($q['options'])): ?>
                        <select name="q<?= $q['questions_id'] ?>" 
                                <?= !empty($q['required']) ? 'required' : '' ?>
                                <?= ($q['field_name'] == 'jenis_peternak') ? 'onchange="toggleDependentFields(this.value)"' : '' ?>>
                            <option value="">-- Pilih --</option>
                            <?php foreach ($q['options'] as $opt): ?>
                                <option value="<?= $opt['option_text'] ?>">
                                    <?= $opt['option_text'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                    <?php elseif ($q['type'] == 'checkbox' && !empty($q['options'])): ?>
                        <?php foreach ($q['options'] as $opt): ?>
                            <div class="options-group">
                                <input type="checkbox" 
                                       name="q<?= $q['questions_id'] ?>[]" 
                                       value="<?= $opt['option_text'] ?>">
                                <?= $opt['option_text'] ?>
                            </div>
                        <?php endforeach; ?>

                    <?php elseif ($q['type'] == 'textarea'): ?>
                        <textarea name="q<?= $q['questions_id'] ?>"
                                  rows="4" 
                                  <?= !empty($q['required']) ? 'required' : '' ?>></textarea>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <input type="submit" name="submit_form" value="Submit">
        <?php endif; ?>
    </form>

    <script>
        function toggleDependentFields(selectedValue) {
            // Hide all dependent fields first
            const dependentFields = document.querySelectorAll('.dependent-field');
            dependentFields.forEach(field => {
                field.style.display = 'none';
                // Remove required attribute when hidden
                const inputs = field.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    input.removeAttribute('required');
                });
            });

            // Show relevant fields based on selection
            if (selectedValue === 'Agen') {
                const agenField = document.getElementById('field-agen_dari');
                if (agenField) {
                    agenField.style.display = 'block';
                    // Add required attribute back
                    const inputs = agenField.querySelectorAll('input, select, textarea');
                    inputs.forEach(input => {
                        input.setAttribute('required', 'required');
                    });
                }
            } else if (selectedValue === 'Sub Agen') {
                const subAgenField = document.getElementById('field-sub_agen_dari');
                if (subAgenField) {
                    subAgenField.style.display = 'block';
                    // Add required attribute back
                    const inputs = subAgenField.querySelectorAll('input, select, textarea');
                    inputs.forEach(input => {
                        input.setAttribute('required', 'required');
                    });
                }
            } else if (selectedValue === 'Kemitraan') {
                const kemitraanField = document.getElementById('field-kemitraan_dari');
                if (kemitraanField) {
                    kemitraanField.style.display = 'block';
                    // Add required attribute back
                    const inputs = kemitraanField.querySelectorAll('input, select, textarea');
                    inputs.forEach(input => {
                        input.setAttribute('required', 'required');
                    });
                }
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Find the jenis peternak select field using data attribute
            const jenisPeternakGroup = document.querySelector('[data-jenis-peternak="true"]');
            let jenisPeternakSelect = null;
            
            if (jenisPeternakGroup) {
                jenisPeternakSelect = jenisPeternakGroup.querySelector('select');
            }
            
            if (jenisPeternakSelect) {
                toggleDependentFields(jenisPeternakSelect.value);
            }
        });
    </script>
</body>
</html>
