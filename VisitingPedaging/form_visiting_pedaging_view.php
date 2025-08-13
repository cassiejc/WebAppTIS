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
        .pakan-question {
            display: none; /* Initially hidden */
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
                $pakan_questions = [];
                $other_questions = [];
                
                foreach ($questions as $q) {
                    if (trim(strtolower($q['question_text'])) === 'jenis ternak pedaging') {
                        $jenis_ternak_q = $q;
                    } elseif (in_array($q['field_name'], ['pakan_pedaging_pullet', 'pakan_pedaging_bebek'])) {
                        $pakan_questions[] = $q;
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
                                    <option value="<?= $opt['option_text'] ?>">
                                        <?= $opt['option_text'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Tampilkan pertanyaan pakan (akan di-show/hide dengan JavaScript) -->
                <?php foreach ($pakan_questions as $q): ?>
                    <div class="form-group pakan-question" data-field="<?= $q['field_name'] ?>" id="pakan_<?= $q['field_name'] ?>">
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
                                               data-field="<?= $q['field_name'] ?>"
                                               <?= !empty($q['required']) ? 'required' : '' ?>> 
                                        <?= $opt['option_text'] ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php elseif ($q['type'] == 'select' && !empty($q['options'])): ?>
                            <select name="q<?= $q['questions_id'] ?>" 
                                    data-field="<?= $q['field_name'] ?>"
                                    <?= !empty($q['required']) ? 'required' : '' ?>>
                                <option value="">-- Pilih Jawaban --</option>
                                <?php foreach ($q['options'] as $opt): ?>
                                    <option value="<?= $opt['option_text'] ?>">
                                        <?= $opt['option_text'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
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
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

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
                                    <label>
                                        <input type="radio" 
                                               name="q<?= $q['questions_id'] ?>" 
                                               value="<?= $opt['option_text'] ?>"
                                               data-field="<?= $q['field_name'] ?>"
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
                                    <option value="<?= $opt['option_text'] ?>">
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
                                    <label>
                                        <input type="checkbox" 
                                               name="q<?= $q['questions_id'] ?>[]" 
                                               value="<?= $opt['option_text'] ?>"
                                               data-field="<?= $q['field_name'] ?>"> 
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
        const jenisTernakSelect = document.getElementById('jenisTernakSelect');
        
        if (jenisTernakSelect) {
            // Hide all pakan questions initially
            hidePakanQuestions();
            
            // Add change event listener
            jenisTernakSelect.addEventListener('change', function() {
                const selectedValue = this.value.toLowerCase();
                showRelevantPakanQuestion(selectedValue);
            });
        }
        
        function hidePakanQuestions() {
            // Hide all pakan questions
            const pakanQuestions = document.querySelectorAll('.pakan-question');
            pakanQuestions.forEach(function(question) {
                question.style.display = 'none';
                // Remove required attribute when hidden
                const inputs = question.querySelectorAll('input, select, textarea');
                inputs.forEach(function(input) {
                    input.removeAttribute('required');
                });
            });
        }
        
        function showRelevantPakanQuestion(jenisTernak) {
            // Hide all pakan questions first
            hidePakanQuestions();
            
            let targetFieldName = '';
            
            // Determine which pakan question to show based on selection
            if (jenisTernak.includes('pullet')) {
                targetFieldName = 'pakan_pedaging_pullet';
            } else if (jenisTernak.includes('bebek')) {
                targetFieldName = 'pakan_pedaging_bebek';
            }
            
            if (targetFieldName) {
                const targetQuestion = document.querySelector(`.pakan-question[data-field="${targetFieldName}"]`);
                if (targetQuestion) {
                    targetQuestion.style.display = 'block';
                    
                    // Add back required attribute if needed
                    const requiredInputs = targetQuestion.querySelectorAll('input[data-required="true"], select[data-required="true"], textarea[data-required="true"]');
                    requiredInputs.forEach(function(input) {
                        input.setAttribute('required', 'required');
                    });
                }
            }
        }
    });
    </script>
</body>
</html>
