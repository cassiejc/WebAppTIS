<!DOCTYPE html>
<html>
<head>
    <title>Form Pedaging</title>
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
            margin-top: 8px; 
        }
        .radio-group label, .checkbox-group label { 
            font-weight: normal; 
            margin-left: 8px; 
            margin-bottom: 8px; 
            display: block; 
            line-height: 1.5; 
        }
        .radio-group input[type="radio"], 
        .checkbox-group input[type="checkbox"] {
            margin-right: 8px; 
            vertical-align: middle; 
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
        .summary-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        .summary-info h4 {
            margin-top: 0;
            color: #007bff;
        }
    </style>
</head>
<body>
    <h2>Form Pedaging - <?php echo $current_sub_area['nama_sub_area']; ?></h2>

    <form method="post" action="" id="pulletForm">
        <div id="pulletQuestions">
            <?php if (!empty($questions)): ?>

                <?php
                // Pisahkan pertanyaan berdasarkan kategori
                $jenis_ternak_q = null;
                $nama_peternak_q = null;
                $nama_farm_q = null;
                $other_questions = [];
                
                foreach ($questions as $q) {
                    if (trim(strtolower($q['question_text'])) === 'jenis ternak pedaging') {
                        $jenis_ternak_q = $q;
                    } elseif ($q['field_name'] === 'nama_peternak') {
                        $nama_peternak_q = $q;
                    } elseif ($q['field_name'] === 'nama_farm') {
                        $nama_farm_q = $q;
                    } else {
                        $other_questions[] = $q;
                    }
                }
                ?>

                <!-- Tampilkan "Jenis Ternak Pedaging" di atas -->
                <?php if ($jenis_ternak_q): ?>
                    <div class="form-group">
                        <label>
                            <?= $jenis_ternak_q['question_text'] ?>
                            <?php if (!empty($jenis_ternak_q['required'])): ?> 
                                <span class="required">*</span> 
                            <?php endif; ?>
                        </label>
                        <?php if ($jenis_ternak_q['type'] == 'select' && !empty($jenis_ternak_q['options'])): ?>
                            <select name="q<?= $jenis_ternak_q['questions_id'] ?>" 
                                    id="jenisTernakSelect"
                                    data-field="<?= $jenis_ternak_q['field_name'] ?>"
                                    <?= !empty($jenis_ternak_q['required']) ? 'required' : '' ?>>
                                <option value="">-- Pilih Jawaban --</option>
                                <?php foreach ($jenis_ternak_q['options'] as $opt): ?>
                                    <option value="<?= $opt['option_text'] ?>" data-tipe="<?= $opt['tipe_ternak'] ?? '' ?>">
                                        <?= $opt['option_text'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Tampilkan "Nama Peternak" setelah Jenis Ternak -->
                <?php if ($nama_peternak_q): ?>
                    <div class="form-group" data-field="<?= $nama_peternak_q['field_name'] ?>">
                        <label>
                            <?= $nama_peternak_q['question_text'] ?>
                            <?php if (!empty($nama_peternak_q['required'])): ?> 
                                <span class="required">*</span> 
                            <?php endif; ?>
                        </label>
                        
                        <?php if ($nama_peternak_q['type'] == 'select' && !empty($nama_peternak_q['options'])): ?>
                            <select name="q<?= $nama_peternak_q['questions_id'] ?>" 
                                    data-field="<?= $nama_peternak_q['field_name'] ?>"
                                    id="namaPeternakSelect"
                                    <?= !empty($nama_peternak_q['required']) ? 'required' : '' ?>>
                                <option value="">-- Pilih Jawaban --</option>
                                <?php foreach ($nama_peternak_q['options'] as $opt): ?>
                                    <option value="<?= $opt['option_text'] ?>" 
                                            data-tipe="<?= $opt['tipe_ternak'] ?? '' ?>" 
                                            class="option-item">
                                        <?= $opt['option_text'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Tampilkan "Nama Farm" setelah Nama Peternak -->
                <?php if ($nama_farm_q): ?>
                    <div class="form-group" data-field="<?= $nama_farm_q['field_name'] ?>">
                        <label>
                            <?= $nama_farm_q['question_text'] ?>
                            <?php if (!empty($nama_farm_q['required'])): ?> 
                                <span class="required">*</span> 
                            <?php endif; ?>
                        </label>
                        
                        <?php if ($nama_farm_q['type'] == 'select' && !empty($nama_farm_q['options'])): ?>
                            <select name="q<?= $nama_farm_q['questions_id'] ?>" 
                                    data-field="<?= $nama_farm_q['field_name'] ?>"
                                    id="namaFarmSelect"
                                    <?= !empty($nama_farm_q['required']) ? 'required' : '' ?>>
                                <option value="">-- Pilih Jawaban --</option>
                                <?php foreach ($nama_farm_q['options'] as $opt): ?>
                                    <option value="<?= $opt['option_text'] ?>" 
                                            data-tipe="<?= $opt['tipe_ternak'] ?? '' ?>" 
                                            class="option-item">
                                        <?= $opt['option_text'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Render pertanyaan lainnya -->
                <?php foreach ($other_questions as $q): ?>
                    <div class="form-group" data-field="<?= $q['field_name'] ?>">
                        <label>
                            <?= $q['question_text'] ?>
                            <?php if (!empty($q['required'])): ?> 
                                <span class="required">*</span> 
                            <?php endif; ?>
                        </label>
                        
                        <?php if ($q['type'] == 'radio' && !empty($q['options'])): ?>
                            <div class="radio-group">
                                <?php foreach ($q['options'] as $opt): ?>
                                    <label data-tipe="<?= $opt['tipe_ternak'] ?? '' ?>" class="option-item">
                                        <input type="radio" 
                                               name="q<?= $q['questions_id'] ?>" 
                                               value="<?= $opt['option_text'] ?>"
                                               data-field="<?= $q['field_name'] ?>"
                                               data-tipe="<?= $opt['tipe_ternak'] ?? '' ?>"
                                               <?= !empty($q['required']) ? 'required' : '' ?>> 
                                        <?= $opt['option_text'] ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php elseif ($q['type'] == 'text'): ?>
                            <input type="text" 
                                   name="q<?= $q['questions_id'] ?>" 
                                   data-field="<?= $q['field_name'] ?>"
                                   placeholder="Masukkan jawaban Anda"
                                   <?= !empty($q['required']) ? 'required' : '' ?>>
                        <?php elseif ($q['type'] == 'textarea'): ?>
                            <textarea name="q<?= $q['questions_id'] ?>" 
                                      data-field="<?= $q['field_name'] ?>"
                                      placeholder="Masukkan jawaban Anda"
                                      <?= !empty($q['required']) ? 'required' : '' ?>></textarea>
                        <?php elseif ($q['type'] == 'select' && !empty($q['options'])): ?>
                            <select name="q<?= $q['questions_id'] ?>" 
                                    data-field="<?= $q['field_name'] ?>"
                                    <?= !empty($q['required']) ? 'required' : '' ?>>
                                <option value="">-- Pilih Jawaban --</option>
                                <?php foreach ($q['options'] as $opt): ?>
                                    <option value="<?= $opt['option_text'] ?>" 
                                            data-tipe="<?= $opt['tipe_ternak'] ?? '' ?>" 
                                            class="option-item">
                                        <?= $opt['option_text'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php elseif ($q['type'] == 'date'): ?>
                            <input type="date" 
                                   name="q<?= $q['questions_id'] ?>" 
                                   data-field="<?= $q['field_name'] ?>"
                                   <?= !empty($q['required']) ? 'required' : '' ?>>
                        <?php elseif ($q['type'] == 'checkbox' && !empty($q['options'])): ?>
                            <div class="checkbox-group">
                                <?php foreach ($q['options'] as $opt): ?>
                                    <label data-tipe="<?= $opt['tipe_ternak'] ?? '' ?>" class="option-item">
                                        <input type="checkbox" 
                                               name="q<?= $q['questions_id'] ?>[]" 
                                               value="<?= $opt['option_text'] ?>"
                                               data-field="<?= $q['field_name'] ?>"
                                               data-tipe="<?= $opt['tipe_ternak'] ?? '' ?>"> 
                                        <?= $opt['option_text'] ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

            <?php else: ?>
                <p class="no-questions">Tidak ada pertanyaan.</p>
            <?php endif; ?>
        </div>
        
        <button type="submit" class="btn-submit" id="submitBtn">Submit</button>
    </form>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const namaPeternakSelect = document.getElementById('namaPeternakSelect');
        const namaFarmSelect = document.getElementById('namaFarmSelect');
        
        // Add change event for nama peternak to filter nama farm
        if (namaPeternakSelect && namaFarmSelect) {
            namaPeternakSelect.addEventListener('change', function() {
                const selectedPeternak = this.value;
                filterFarmByPeternak(selectedPeternak);
            });
        }
        
        function filterFarmByPeternak(selectedPeternak) {
            if (!namaFarmSelect || !selectedPeternak) {
                return;
            }
            
            // Reset nama farm select
            namaFarmSelect.selectedIndex = 0;
            
            // Filter farm options based on selected peternak
            const farmOptions = namaFarmSelect.querySelectorAll('option.option-item');
            farmOptions.forEach(function(option) {
                const optionText = option.textContent.toLowerCase();
                const peternakText = selectedPeternak.toLowerCase();
                
                // You can customize this logic based on how farm names relate to peternak names
                // For now, assuming farm names contain peternak names or are related
                const peternakMatch = optionText.includes(peternakText) || 
                                    peternakText.includes(optionText.split(' ')[0]); // Basic matching
                
                if (peternakMatch) {
                    option.style.display = 'block';
                    option.disabled = false;
                } else {
                    option.style.display = 'none';
                    option.disabled = true;
                }
            });
        }
    });
    </script>
</body>
</html>
