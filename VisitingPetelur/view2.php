<!DOCTYPE html>
<html>
<head>
    <title>Form Petelur</title>
    <style>
        form {
            margin-left: 20px;
        }

        h2 {
            margin-left: 10px;
        }

        /* Add styling for form controls */
        .form-control {
            width: 300px; /* Atur lebar sesuai kebutuhan */
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        input[type="date"] {
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
            width: 200px; /* Lebar khusus untuk input date */
        }

        .form-group {
            margin-bottom: 15px;
            max-width: 500px; /* Batasi lebar maksimum form group */
        }

        select.form-control {
            height: 35px; /* Tinggi untuk select box */
        }

        .loading {
            display: none;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <h2>Form Petelur</h2>

    <form method="post" action="">
        <input type="hidden" id="selected_tipe_ternak" name="tipe_ternak" value="">
        
        <div id="questions-container">
            <!-- Questions will be loaded here dynamically -->
            <?php if (!empty($questions)): ?>
                <?php foreach ($questions as $q): ?>
                    <div class="form-group question-group" style="margin-bottom: 20px;">
                        <label>
                            <?= $q['question_text'] ?>
                            <?php if (!empty($q['required'])): ?> <span style="color: red">*</span> <?php endif; ?>
                        </label>
                        <br>
                        <?php if ($q['type'] === 'select' && !empty($q['options'])): ?>
                            <select name="q<?= $q['questions_id'] ?>" class="form-control" 
                                    <?php if ($q['field_name'] === 'tipe_ternak'): ?>onchange="changeTipeTermak(this.value)"<?php endif; ?>>
                                <option value="">-- Pilih --</option>
                                <?php foreach ($q['options'] as $opt): ?>
                                    <option value="<?= $opt['option_text'] ?>">
                                        <?= $opt['option_text'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        
                        <?php elseif ($q['type'] === 'radio' && !empty($q['options'])): ?>
                            <?php foreach ($q['options'] as $opt): ?>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="q<?= $q['questions_id'] ?>" 
                                               value="<?= $opt['option_text'] ?>" <?= !empty($q['required']) ? 'required' : '' ?>>
                                        <?= $opt['option_text'] ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php elseif ($q['type'] === 'date'): ?>
                            <input type="date" name="q<?= $q['questions_id'] ?>" 
                                   class="form-control"
                                   <?= !empty($q['required']) ? 'required' : '' ?>><br>
                        <?php elseif ($q['type'] === 'text'): ?>
                            <input type="text" name="q<?= $q['questions_id'] ?>" 
                                   class="form-control"
                                   <?= !empty($q['required']) ? 'required' : '' ?>><br>
                        <?php elseif ($q['type'] === 'number'): ?>
                            <input type="number" name="q<?= $q['questions_id'] ?>" 
                                   class="form-control"
                                   <?= !empty($q['required']) ? 'required' : '' ?>><br>
                        <?php elseif ($q['type'] === 'textarea'): ?>
                            <textarea name="q<?= $q['questions_id'] ?>" 
                                      class="form-control" rows="3"
                                      <?= !empty($q['required']) ? 'required' : '' ?>></textarea><br>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Tidak ada pertanyaan.</p>
            <?php endif; ?>
        </div>
        
        <div class="loading" id="loading">Memuat pertanyaan...</div>
        
        <input type="submit" value="Submit" id="submit-btn">
    </form>

    <script>
        function changeTipeTermak(tipeTermak) {
            // Update hidden field
            document.getElementById('selected_tipe_ternak').value = tipeTermak;
            
            // Show loading
            document.getElementById('loading').style.display = 'block';
            document.getElementById('submit-btn').disabled = true;
            
            // Make AJAX request to get questions
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '<?= base_url('Visiting_Petelur_Controller/get_questions_by_type') ?>', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        var questions = JSON.parse(xhr.responseText);
                        updateQuestionsDisplay(questions);
                        
                        // Hide loading
                        document.getElementById('loading').style.display = 'none';
                        document.getElementById('submit-btn').disabled = false;
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        // Hide loading
                        document.getElementById('loading').style.display = 'none';
                        document.getElementById('submit-btn').disabled = false;
                    }
                }
            };
            
            xhr.send('tipe_ternak=' + encodeURIComponent(tipeTermak));
        }
        
        function updateQuestionsDisplay(questions) {
            var container = document.getElementById('questions-container');
            container.innerHTML = '';
            
            if (questions && questions.length > 0) {
                questions.forEach(function(q) {
                    var formGroup = document.createElement('div');
                    formGroup.className = 'form-group question-group';
                    formGroup.style.marginBottom = '20px';
                    
                    var label = document.createElement('label');
                    label.innerHTML = q.question_text;
                    if (q.required) {
                        label.innerHTML += ' <span style="color: red">*</span>';
                    }
                    
                    var br = document.createElement('br');
                    
                    formGroup.appendChild(label);
                    formGroup.appendChild(br);
                    
                    if (q.type === 'select' && q.options && q.options.length > 0) {
                        var select = document.createElement('select');
                        select.name = 'q' + q.questions_id;
                        select.className = 'form-control';
                        
                        // Add special handling for tipe_ternak field
                        if (q.field_name === 'tipe_ternak') {
                            select.onchange = function() {
                                changeTipeTermak(this.value);
                            };
                        }
                        
                        var defaultOption = document.createElement('option');
                        defaultOption.value = '';
                        defaultOption.textContent = '-- Pilih --';
                        select.appendChild(defaultOption);
                        
                        q.options.forEach(function(opt) {
                            var option = document.createElement('option');
                            option.value = opt.option_text;
                            option.textContent = opt.option_text;
                            
                            // Pre-select tipe_ternak if it matches current selection
                            if (q.field_name === 'tipe_ternak' && opt.option_text === document.getElementById('selected_tipe_ternak').value) {
                                option.selected = true;
                            }
                            
                            select.appendChild(option);
                        });
                        
                        formGroup.appendChild(select);
                        
                    } else if (q.type === 'radio' && q.options && q.options.length > 0) {
                        q.options.forEach(function(opt) {
                            var radioDiv = document.createElement('div');
                            radioDiv.className = 'radio';
                            
                            var radioLabel = document.createElement('label');
                            var radioInput = document.createElement('input');
                            radioInput.type = 'radio';
                            radioInput.name = 'q' + q.questions_id;
                            radioInput.value = opt.option_text;
                            if (q.required) radioInput.required = true;
                            
                            radioLabel.appendChild(radioInput);
                            radioLabel.appendChild(document.createTextNode(' ' + opt.option_text));
                            radioDiv.appendChild(radioLabel);
                            formGroup.appendChild(radioDiv);
                        });
                        
                    } else if (q.type === 'date') {
                        var input = document.createElement('input');
                        input.type = 'date';
                        input.name = 'q' + q.questions_id;
                        input.className = 'form-control';
                        if (q.required) input.required = true;
                        formGroup.appendChild(input);
                        
                    } else if (q.type === 'text') {
                        var input = document.createElement('input');
                        input.type = 'text';
                        input.name = 'q' + q.questions_id;
                        input.className = 'form-control';
                        if (q.required) input.required = true;
                        formGroup.appendChild(input);
                        
                    } else if (q.type === 'number') {
                        var input = document.createElement('input');
                        input.type = 'number';
                        input.name = 'q' + q.questions_id;
                        input.className = 'form-control';
                        if (q.required) input.required = true;
                        formGroup.appendChild(input);
                        
                    } else if (q.type === 'textarea') {
                        var textarea = document.createElement('textarea');
                        textarea.name = 'q' + q.questions_id;
                        textarea.className = 'form-control';
                        textarea.rows = 3;
                        if (q.required) textarea.required = true;
                        formGroup.appendChild(textarea);
                    }
                    
                    container.appendChild(formGroup);
                });
            } else {
                container.innerHTML = '<p>Tidak ada pertanyaan.</p>';
            }
        }
    </script>

</body>
</html>
