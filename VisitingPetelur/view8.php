<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Petelur</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .currency-input {
            position: relative;
        }
        .currency-prefix {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            pointer-events: none;
            z-index: 5;
        }
        .currency-input input {
            padding-left: 35px;
        }
        .dropdown-search-input {
            border: none;
            border-bottom: 1px solid #dee2e6;
            border-radius: 0;
        }
        .dropdown-search-input:focus {
            box-shadow: none;
            border-bottom-color: #0d6efd;
        }
        .dropdown-menu {
            max-height: 200px;
            overflow-y: auto;
        }
        .btn-outline-secondary:disabled {
            background-color: #f8f9fa;
            border-color: #dee2e6;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2 class="mb-4">Petelur</h2>

        <form method="post" action="" class="needs-validation" novalidate>
            <input type="hidden" id="selected_tipe_ternak" name="tipe_ternak" value="">
            
            <div id="questions-container">
                <?php if (!empty($questions)): ?>
                    <?php foreach ($questions as $q): ?>
                        <div class="mb-3 question-group">
                            <label class="form-label">
                                <?= $q['question_text'] ?>
                                <?php if (!empty($q['required']) && $q['required'] == 1): ?>
                                    <span class="text-danger">*</span>
                                <?php endif; ?>
                            </label>
                            
                            <?php if ($q['type'] === 'select' && !empty($q['options'])): ?>
                                <?php if ($q['field_name'] === 'nama_farm'): ?>
                                    <div class="dropdown w-100">
                                        <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start <?php if ($q['field_name'] === 'nama_farm' || strpos($q['field_name'], 'strain') !== false): ?>disabled<?php endif; ?>" 
                                                type="button" 
                                                id="dropbtn_<?= $q['questions_id'] ?>"
                                                data-bs-toggle="dropdown"
                                                <?php if ($q['field_name'] === 'nama_farm' || strpos($q['field_name'], 'strain') !== false): ?>disabled<?php endif; ?>>
                                            -- Pilih Nama Farm --
                                        </button>
                                        <ul class="dropdown-menu w-100" id="dropdown_<?= $q['questions_id'] ?>">
                                            <li>
                                                <input type="text" 
                                                       placeholder="Search..." 
                                                       class="form-control dropdown-search-input mx-2" 
                                                       style="width: calc(100% - 1rem);"
                                                       onkeyup="filterDropdown('dropdown_<?= $q['questions_id'] ?>')"
                                                       onclick="event.stopPropagation();">
                                            </li>
                                            <?php foreach ($q['options'] as $opt): ?>
                                                <li><a class="dropdown-item" href="#" onclick="selectOption('<?= $q['questions_id'] ?>', '<?= htmlspecialchars($opt['option_text'], ENT_QUOTES) ?>')"><?= $opt['option_text'] ?></a></li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <input type="hidden" name="q<?= $q['questions_id'] ?>" id="hidden_<?= $q['questions_id'] ?>" <?= (!empty($q['required']) && $q['required'] == 1) ? 'required' : '' ?>>
                                    </div>
                                <?php else: ?>
                                    <select name="q<?= $q['questions_id'] ?>" class="form-select" 
                                            <?php if ($q['field_name'] === 'tipe_ternak'): ?>onchange="changeTipeTermak(this.value)"<?php endif; ?>
                                            <?php if (strpos($q['field_name'], 'strain') !== false): ?>disabled<?php endif; ?>
                                            <?= (!empty($q['required']) && $q['required'] == 1) ? 'required' : '' ?>>
                                        <option value="">-- Pilih --</option>
                                        <?php foreach ($q['options'] as $opt): ?>
                                            <option value="<?= $opt['option_text'] ?>"><?= $opt['option_text'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php endif; ?>
                            
                            <?php elseif ($q['type'] === 'radio' && !empty($q['options'])): ?>
                                <?php foreach ($q['options'] as $opt): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="q<?= $q['questions_id'] ?>" 
                                               id="radio_<?= $q['questions_id'] ?>_<?= $opt['option_id'] ?>"
                                               value="<?= $opt['option_text'] ?>" <?= (!empty($q['required']) && $q['required'] == 1) ? 'required' : '' ?>>
                                        <label class="form-check-label" for="radio_<?= $q['questions_id'] ?>_<?= $opt['option_id'] ?>">
                                            <?= $opt['option_text'] ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                                
                            <?php elseif ($q['type'] === 'date'): ?>
                                <input type="date" name="q<?= $q['questions_id'] ?>" 
                                       class="form-control"
                                       <?= (!empty($q['required']) && $q['required'] == 1) ? 'required' : '' ?>>
                                       
                            <?php elseif ($q['type'] === 'text'): ?>
                                <?php if (isset($q['input_type']) && $q['input_type'] === 'integer'): ?>
                                    <input type="text" name="q<?= $q['questions_id'] ?>" 
                                           class="form-control integer-field"
                                           onkeypress="return isIntegerKey(event)"
                                           oninput="formatIntegerWithComma(this)"
                                           onpaste="handleIntegerPaste(event)"
                                           <?= (!empty($q['required']) && $q['required'] == 1) ? 'required' : '' ?>>
                                           
                                <?php elseif (isset($q['input_type']) && $q['input_type'] === 'currency'): ?>
                                    <div class="currency-input">
                                        <span class="currency-prefix">Rp</span>
                                        <input type="text" name="q<?= $q['questions_id'] ?>" 
                                               class="form-control"
                                               oninput="formatCurrencyWithComma(this)"
                                               onkeypress="return isNumberKey(event)"
                                               <?= (!empty($q['required']) && $q['required'] == 1) ? 'required' : '' ?>>
                                    </div>
                                    
                                <?php elseif (isset($q['input_type']) && $q['input_type'] === 'letters_only'): ?>
                                    <input type="text" name="q<?= $q['questions_id'] ?>" 
                                           class="form-control letters-only-field"
                                           onkeypress="return isLetterKey(event)"
                                           oninput="filterLettersOnly(this)"
                                           onpaste="handleLettersOnlyPaste(event)"
                                           <?= (!empty($q['required']) && $q['required'] == 1) ? 'required' : '' ?>>
                                           
                                <?php else: ?>
                                    <input type="text" name="q<?= $q['questions_id'] ?>" 
                                           class="form-control"
                                           <?= (!empty($q['required']) && $q['required'] == 1) ? 'required' : '' ?>>
                                <?php endif; ?>
                                
                            <?php elseif ($q['type'] === 'number'): ?>
                                <?php if (isset($q['input_type']) && $q['input_type'] === 'integer'): ?>
                                    <input type="text" name="q<?= $q['questions_id'] ?>" 
                                           class="form-control integer-field"
                                           onkeypress="return isIntegerKey(event)"
                                           oninput="formatIntegerWithComma(this)"
                                           onpaste="handleIntegerPaste(event)"
                                           <?= (!empty($q['required']) && $q['required'] == 1) ? 'required' : '' ?>>
                                <?php else: ?>
                                    <input type="number" name="q<?= $q['questions_id'] ?>" 
                                           class="form-control"
                                           step="<?= isset($q['step']) ? $q['step'] : '0.01' ?>"
                                           min="0"
                                           <?= (!empty($q['required']) && $q['required'] == 1) ? 'required' : '' ?>>
                                <?php endif; ?>
                                
                            <?php elseif ($q['type'] === 'textarea'): ?>
                                <textarea name="q<?= $q['questions_id'] ?>" 
                                          class="form-control" rows="3"
                                          <?= (!empty($q['required']) && $q['required'] == 1) ? 'required' : '' ?>></textarea>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info">Tidak ada pertanyaan.</div>
                <?php endif; ?>
            </div>
            
            <div class="alert alert-info d-none" id="loading">
                <div class="d-flex align-items-center">
                    <div class="spinner-border spinner-border-sm me-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    Memuat pertanyaan...
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" id="submit-btn">Submit</button>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Input validation functions
        function isLetterKey(evt) {
            const charCode = evt.which || evt.keyCode;
            if (charCode === 46 || charCode === 8 || charCode === 9 || 
                charCode === 27 || charCode === 13 || charCode === 32 ||
                (charCode >= 37 && charCode <= 40)) {
                return true;
            }
            if ((charCode < 65 || charCode > 90) && (charCode < 97 || charCode > 122)) {
                evt.preventDefault();
                return false;
            }
            return true;
        }

        function filterLettersOnly(input) {
            let value = input.value.replace(/[^a-zA-Z\s]/g, '');
            value = value.replace(/\s+/g, ' ').trim();
            input.value = value;
        }

        function handleLettersOnlyPaste(evt) {
            evt.preventDefault();
            let paste = (evt.clipboardData || window.clipboardData).getData('text');
            let cleanValue = paste.replace(/[^a-zA-Z\s]/g, '');
            cleanValue = cleanValue.replace(/\s+/g, ' ').trim();
            evt.target.value = cleanValue;
        }

        function isIntegerKey(evt) {
            const charCode = evt.which || evt.keyCode;
            if (charCode === 46 || charCode === 8 || charCode === 9 || 
                charCode === 27 || charCode === 13 || 
                (charCode >= 37 && charCode <= 40)) {
                return true;
            }
            if (charCode < 48 || charCode > 57) {
                evt.preventDefault();
                return false;
            }
            return true;
        }

        function formatIntegerWithComma(input) {
            let value = input.value.replace(/[^\d]/g, '');
            if (value === '') {
                input.value = '';
                return;
            }
            let formatted = parseInt(value, 10).toLocaleString('en-US');
            input.value = formatted;
        }

        function handleIntegerPaste(evt) {
            evt.preventDefault();
            let paste = (evt.clipboardData || window.clipboardData).getData('text');
            let cleanValue = paste.replace(/[^\d]/g, '');
            if (cleanValue !== '') {
                let formatted = parseInt(cleanValue, 10).toLocaleString('en-US');
                evt.target.value = formatted;
            }
        }

        function formatCurrencyWithComma(input) {
            let value = input.value.replace(/[^\d]/g, '');
            if (value === '') {
                input.value = '';
                return;
            }
            let formatted = parseInt(value).toLocaleString('en-US');
            input.value = formatted;
        }

        function isNumberKey(evt) {
            var charCode = evt.which || evt.keyCode;
            if (charCode == 46 || charCode == 8 || charCode == 9 || charCode == 27 || charCode == 13) {
                return true;
            }
            if ((charCode < 48 || charCode > 57)) {
                return false;
            }
            return true;
        }

        // Dropdown functions
        function filterDropdown(dropdownId) {
            const input = document.querySelector(`#${dropdownId} .dropdown-search-input`);
            const filter = input.value.toUpperCase();
            const items = document.querySelectorAll(`#${dropdownId} .dropdown-item`);
            
            items.forEach(item => {
                const txtValue = item.textContent || item.innerText;
                item.parentElement.style.display = txtValue.toUpperCase().indexOf(filter) > -1 ? "" : "none";
            });
        }

        function selectOption(questionId, optionText) {
            document.getElementById(`dropbtn_${questionId}`).textContent = optionText;
            document.getElementById(`hidden_${questionId}`).value = optionText;
            
            // Close dropdown using Bootstrap
            const dropdown = bootstrap.Dropdown.getInstance(document.getElementById(`dropbtn_${questionId}`));
            if (dropdown) dropdown.hide();
        }

        function enableDependentDropdowns(enable) {
            const namaFarmButtons = document.querySelectorAll('[id^="dropbtn_"]');
            namaFarmButtons.forEach(button => {
                const questionGroup = button.closest('.question-group');
                const label = questionGroup.querySelector('label');
                
                if (label && (label.textContent.includes('Nama Farm') || label.textContent.includes('nama_farm'))) {
                    button.disabled = !enable;
                    if (enable) {
                        button.classList.remove('disabled');
                    } else {
                        button.classList.add('disabled');
                        button.textContent = '-- Pilih Nama Farm --';
                        const hiddenInput = questionGroup.querySelector('input[type="hidden"]');
                        if (hiddenInput) hiddenInput.value = '';
                    }
                }
            });

            const strainSelects = document.querySelectorAll('select.form-select');
            strainSelects.forEach(select => {
                const questionGroup = select.closest('.question-group');
                const label = questionGroup.querySelector('label');
                
                if (label && (label.textContent.toLowerCase().includes('strain') || select.name.includes('strain'))) {
                    select.disabled = !enable;
                    if (!enable) select.value = '';
                }
            });
        }

        function changeTipeTermak(tipeTermak) {
            document.getElementById('selected_tipe_ternak').value = tipeTermak;
            
            if (tipeTermak === '') {
                enableDependentDropdowns(false);
                return;
            }
            
            enableDependentDropdowns(true);
            
            document.getElementById('loading').classList.remove('d-none');
            document.getElementById('submit-btn').disabled = true;
            
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '<?= base_url('Visiting_Petelur_Controller/get_questions_by_type') ?>', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        var questions = JSON.parse(xhr.responseText);
                        updateQuestionsDisplay(questions);
                        
                        document.getElementById('loading').classList.add('d-none');
                        document.getElementById('submit-btn').disabled = false;
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        document.getElementById('loading').classList.add('d-none');
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
                    formGroup.className = 'mb-3 question-group';
                    
                    var label = document.createElement('label');
                    label.className = 'form-label';
                    label.innerHTML = q.question_text;
                    if (q.required == 1) {
                        label.innerHTML += ' <span class="text-danger">*</span>';
                    }
                    
                    formGroup.appendChild(label);
                    
                    if (q.type === 'select' && q.options && q.options.length > 0) {
                        if (q.field_name === 'nama_farm' || q.field_name === 'layer_nama_farm') {
                            var dropdown = document.createElement('div');
                            dropdown.className = 'dropdown w-100';
                            
                            var dropbtn = document.createElement('button');
                            dropbtn.type = 'button';
                            dropbtn.className = 'btn btn-outline-secondary dropdown-toggle w-100 text-start';
                            dropbtn.id = 'dropbtn_' + q.questions_id;
                            dropbtn.textContent = '-- Pilih Nama Farm --';
                            dropbtn.setAttribute('data-bs-toggle', 'dropdown');
                            
                            var dropdownMenu = document.createElement('ul');
                            dropdownMenu.id = 'dropdown_' + q.questions_id;
                            dropdownMenu.className = 'dropdown-menu w-100';
                            
                            var searchLi = document.createElement('li');
                            var searchInput = document.createElement('input');
                            searchInput.type = 'text';
                            searchInput.placeholder = 'Search...';
                            searchInput.className = 'form-control dropdown-search-input mx-2';
                            searchInput.style.width = 'calc(100% - 1rem)';
                            searchInput.onkeyup = function() {
                                filterDropdown('dropdown_' + q.questions_id);
                            };
                            searchInput.onclick = function(event) {
                                event.stopPropagation();
                            };
                            searchLi.appendChild(searchInput);
                            dropdownMenu.appendChild(searchLi);
                            
                            q.options.forEach(function(opt) {
                                var li = document.createElement('li');
                                var a = document.createElement('a');
                                a.className = 'dropdown-item';
                                a.href = '#';
                                a.textContent = opt.option_text;
                                a.onclick = function() {
                                    selectOption(q.questions_id, opt.option_text);
                                };
                                li.appendChild(a);
                                dropdownMenu.appendChild(li);
                            });
                            
                            var hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = 'q' + q.questions_id;
                            hiddenInput.id = 'hidden_' + q.questions_id;
                            if (q.required == 1) hiddenInput.required = true;
                            
                            dropdown.appendChild(dropbtn);
                            dropdown.appendChild(dropdownMenu);
                            formGroup.appendChild(dropdown);
                            formGroup.appendChild(hiddenInput);
                            
                        } else {
                            var select = document.createElement('select');
                            select.name = 'q' + q.questions_id;
                            select.className = 'form-select';
                            
                            if (q.field_name === 'tipe_ternak') {
                                select.onchange = function() {
                                    changeTipeTermak(this.value);
                                };
                            }
                            
                            if (q.field_name.toLowerCase().includes('strain')) {
                                select.disabled = true;
                            }
                            
                            if (q.required == 1) select.required = true;
                            
                            var defaultOption = document.createElement('option');
                            defaultOption.value = '';
                            defaultOption.textContent = '-- Pilih --';
                            select.appendChild(defaultOption);
                            
                            q.options.forEach(function(opt) {
                                var option = document.createElement('option');
                                option.value = opt.option_text;
                                option.textContent = opt.option_text;
                                
                                if (q.field_name === 'tipe_ternak' && opt.option_text === document.getElementById('selected_tipe_ternak').value) {
                                    option.selected = true;
                                }
                                
                                select.appendChild(option);
                            });
                            
                            formGroup.appendChild(select);
                        }
                        
                    } else if (q.type === 'radio' && q.options && q.options.length > 0) {
                        q.options.forEach(function(opt, index) {
                            var radioDiv = document.createElement('div');
                            radioDiv.className = 'form-check';
                            
                            var radioInput = document.createElement('input');
                            radioInput.type = 'radio';
                            radioInput.name = 'q' + q.questions_id;
                            radioInput.className = 'form-check-input';
                            radioInput.id = 'radio_' + q.questions_id + '_' + index;
                            radioInput.value = opt.option_text;
                            if (q.required == 1) radioInput.required = true;
                            
                            var radioLabel = document.createElement('label');
                            radioLabel.className = 'form-check-label';
                            radioLabel.setAttribute('for', 'radio_' + q.questions_id + '_' + index);
                            radioLabel.textContent = opt.option_text;
                            
                            radioDiv.appendChild(radioInput);
                            radioDiv.appendChild(radioLabel);
                            formGroup.appendChild(radioDiv);
                        });
                        
                    } else if (q.type === 'date') {
                        var input = document.createElement('input');
                        input.type = 'date';
                        input.name = 'q' + q.questions_id;
                        input.className = 'form-control';
                        if (q.required == 1) input.required = true;
                        formGroup.appendChild(input);
                        
                    } else if (q.type === 'text' || q.type === 'number') {
                        if (q.input_type === 'integer') {
                            var input = document.createElement('input');
                            input.type = 'text';
                            input.name = 'q' + q.questions_id;
                            input.className = 'form-control integer-field';
                            input.onkeypress = function(event) {
                                return isIntegerKey(event);
                            };
                            input.oninput = function() {
                                formatIntegerWithComma(this);
                            };
                            input.onpaste = function(event) {
                                handleIntegerPaste(event);
                            };
                            if (q.required == 1) input.required = true;
                            formGroup.appendChild(input);
                            
                        } else if (q.input_type === 'currency') {
                            var currencyDiv = document.createElement('div');
                            currencyDiv.className = 'currency-input';
                            
                            var currencyPrefix = document.createElement('span');
                            currencyPrefix.className = 'currency-prefix';
                            currencyPrefix.textContent = 'Rp';
                            
                            var input = document.createElement('input');
                            input.type = 'text';
                            input.name = 'q' + q.questions_id;
                            input.className = 'form-control';
                            input.oninput = function() {
                                formatCurrencyWithComma(this);
                            };
                            input.onkeypress = function(event) {
                                return isNumberKey(event);
                            };
                            if (q.required == 1) input.required = true;
                            
                            currencyDiv.appendChild(currencyPrefix);
                            currencyDiv.appendChild(input);
                            formGroup.appendChild(currencyDiv);
                            
                        } else if (q.input_type === 'letters_only') {
                            var input = document.createElement('input');
                            input.type = 'text';
                            input.name = 'q' + q.questions_id;
                            input.className = 'form-control letters-only-field';
                            input.onkeypress = function(event) {
                                return isLetterKey(event);
                            };
                            input.oninput = function() {
                                filterLettersOnly(this);
                            };
                            input.onpaste = function(event) {
                                handleLettersOnlyPaste(event);
                            };
                            if (q.required == 1) input.required = true;
                            formGroup.appendChild(input);
                            
                        } else if (q.type === 'number' && q.input_type !== 'integer') {
                            var input = document.createElement('input');
                            input.type = 'number';
                            input.name = 'q' + q.questions_id;
                            input.className = 'form-control';
                            input.step = q.step || '0.01';
                            input.min = '0';
                            if (q.required == 1) input.required = true;
                            formGroup.appendChild(input);
                            
                        } else {
                            var input = document.createElement('input');
                            input.type = 'text';
                            input.name = 'q' + q.questions_id;
                            input.className = 'form-control';
                            if (q.required == 1) input.required = true;
                            formGroup.appendChild(input);
                        }
                        
                    } else if (q.type === 'textarea') {
                        var textarea = document.createElement('textarea');
                        textarea.name = 'q' + q.questions_id;
                        textarea.className = 'form-control';
                        textarea.rows = 3;
                        if (q.required == 1) textarea.required = true;
                        formGroup.appendChild(textarea);
                    }
                    
                    container.appendChild(formGroup);
                });
                
                enableDependentDropdowns(true);
            } else {
                container.innerHTML = '<div class="alert alert-info">Tidak ada pertanyaan.</div>';
            }
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            enableDependentDropdowns(false);
        });
    </script>
</body>
</html>
