<!DOCTYPE html>
<html>
<head>
    <title><?= isset($page_title) ? $page_title : 'Form Tambah Data Baru' ?></title>
    <style>
        form { margin-left: 20px; }
        h2 { margin-left: 10px; }
        .form-group { margin-bottom: 20px; }
        .options-group { margin: 5px 0; }
        .dependent-field { display: none; }
    </style>
</head>
<body>
    <h2><?= isset($page_title) ? $page_title : 'Form Tambah Data Baru' ?></h2>

    <form method="post" action="">
        <?php if (!empty($questions_kategori)): ?>
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
                    
                    <?php if ($q['type'] == 'number' || $q['field_name'] == 'kapasitas_peternak' || $q['field_name'] == 'jumlah_kandang_peternak' || $q['field_name'] == 'kapasitas_farm'): ?>
                        <input type="text"
                               inputmode="numeric" 
                               class="numeric-input"
                               name="q<?= $q['questions_id'] ?>" 
                               <?= !empty($q['required']) ? 'required' : '' ?>>

                    <?php elseif ($q['type'] == 'text'): ?>
                        <input type="text" 
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
        <?php else: ?>
            <p>Tidak ada pertanyaan yang tersedia untuk kategori ini.</p>
        <?php endif; ?>
    </form>

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
