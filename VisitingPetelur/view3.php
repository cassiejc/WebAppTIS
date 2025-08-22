<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Petelur</title>
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
            margin-bottom: 8px; /* Konsisten untuk semua */
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

        /* Dropdown Search Styles */
        .dropbtn {
            background-color: #ffffff;
            color: #333333;
            padding: 10px 16px;
            font-size: 14px;
            border: 1px solid #cccccc;
            cursor: pointer;
            width: 300px;
            text-align: left;
            border-radius: 4px;
        }

        .dropbtn:hover, .dropbtn:focus {
            background-color: #f5f5f5;
            border-color: #999999;
        }

        .dropdown-search-input {
            box-sizing: border-box;
            font-size: 14px;
            padding: 10px 15px;
            border: none;
            border-bottom: 1px solid #ddd;
            width: 100%;
        }

        .dropdown-search-input:focus {
            outline: 2px solid #666666;
        }

        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #ffffff;
            min-width: 300px;
            max-height: 200px;
            overflow: auto;
            border: 1px solid #cccccc;
            z-index: 1000;
            border-radius: 4px;
        }

        .dropdown-content a {
            color: #333333;
            padding: 10px 15px;
            text-decoration: none;
            display: block;
            cursor: pointer;
        }

        .dropdown-content a:hover {
            background-color: #f5f5f5;
        }

        .show {
            display: block;
        }

        /* Submit button styling */
        input[type="submit"] {
            margin-top: 15px;
            padding: 8px 20px;
        }
    </style>
</head>
<body>
    <h2>Petelur</h2>

    <form method="post" action="">
        <input type="hidden" id="selected_tipe_ternak" name="tipe_ternak" value="">
        
        <div id="questions-container">
            <!-- Questions will be loaded here dynamically -->
            <?php if (!empty($questions)): ?>
                <?php foreach ($questions as $q): ?>
                    <div class="form-group question-group">
                        <label>
                            <?= $q['question_text'] ?>
                            <?php if (!empty($q['required'])): ?> <span style="color: red">*</span> <?php endif; ?>
                        </label>
                        <br>
                        <?php if ($q['type'] === 'select' && !empty($q['options'])): ?>
                            <?php if ($q['field_name'] === 'nama_farm'): ?>
                                <!-- Dropdown Search for nama_farm only -->
                                <div class="dropdown">
                                    <button onclick="toggleDropdown('dropdown_<?= $q['questions_id'] ?>')" 
                                            class="dropbtn" type="button" 
                                            id="dropbtn_<?= $q['questions_id'] ?>">-- Pilih Nama Farm --</button>
                                    <div id="dropdown_<?= $q['questions_id'] ?>" class="dropdown-content">
                                        <input type="text" 
                                               placeholder="Search..." 
                                               class="dropdown-search-input" 
                                               onkeyup="filterDropdown('dropdown_<?= $q['questions_id'] ?>')"
                                               onclick="event.stopPropagation();">
                                        <?php foreach ($q['options'] as $opt): ?>
                                            <a onclick="selectOption('<?= $q['questions_id'] ?>', '<?= htmlspecialchars($opt['option_text'], ENT_QUOTES) ?>')"><?= $opt['option_text'] ?></a>
                                        <?php endforeach; ?>
                                    </div>
                                    <!-- Hidden input to store the selected value -->
                                    <input type="hidden" name="q<?= $q['questions_id'] ?>" id="hidden_<?= $q['questions_id'] ?>" <?= !empty($q['required']) ? 'required' : '' ?>>
                                </div>
                            <?php else: ?>
                                <!-- Regular select for other fields, with special handling for tipe_ternak -->
                                <select name="q<?= $q['questions_id'] ?>" class="form-control" 
                                        <?php if ($q['field_name'] === 'tipe_ternak'): ?>onchange="changeTipeTermak(this.value)"<?php endif; ?>
                                        <?= !empty($q['required']) ? 'required' : '' ?>>
                                    <option value="">-- Pilih --</option>
                                    <?php foreach ($q['options'] as $opt): ?>
                                        <option value="<?= $opt['option_text'] ?>">
                                            <?= $opt['option_text'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        
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
                                   <?= !empty($q['required']) ? 'required' : '' ?>>
                        <?php elseif ($q['type'] === 'text'): ?>
                            <input type="text" name="q<?= $q['questions_id'] ?>" 
                                   class="form-control"
                                   <?= !empty($q['required']) ? 'required' : '' ?>>
                        <?php elseif ($q['type'] === 'number'): ?>
                            <input type="number" name="q<?= $q['questions_id'] ?>" 
                                   class="form-control"
                                   <?= !empty($q['required']) ? 'required' : '' ?>>
                        <?php elseif ($q['type'] === 'textarea'): ?>
                            <textarea name="q<?= $q['questions_id'] ?>" 
                                      class="form-control" rows="3"
                                      <?= !empty($q['required']) ? 'required' : '' ?>></textarea>
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
        // Toggle dropdown visibility
        function toggleDropdown(dropdownId) {
            document.getElementById(dropdownId).classList.toggle("show");
        }

        // Filter dropdown options based on search input
        function filterDropdown(dropdownId) {
            const input = document.querySelector(`#${dropdownId} .dropdown-search-input`);
            const filter = input.value.toUpperCase();
            const div = document.getElementById(dropdownId);
            const a = div.getElementsByTagName("a");
            
            for (let i = 0; i < a.length; i++) {
                const txtValue = a[i].textContent || a[i].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    a[i].style.display = "";
                } else {
                    a[i].style.display = "none";
                }
            }
        }

        // Select option and update button text and hidden input
        function selectOption(questionId, optionText) {
            // Update button text
            document.getElementById(`dropbtn_${questionId}`).textContent = optionText;
            
            // Update hidden input value
            document.getElementById(`hidden_${questionId}`).value = optionText;
            
            // Close dropdown
            document.getElementById(`dropdown_${questionId}`).classList.remove("show");
        }

        // Close dropdown when clicking outside
        window.onclick = function(event) {
            if (!event.target.matches('.dropbtn')) {
                const dropdowns = document.getElementsByClassName("dropdown-content");
                for (let i = 0; i < dropdowns.length; i++) {
                    const openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }

        // Function for handling tipe_ternak change
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
                    
                    var label = document.createElement('label');
                    label.innerHTML = q.question_text;
                    if (q.required) {
                        label.innerHTML += ' <span style="color: red">*</span>';
                    }
                    
                    var br = document.createElement('br');
                    
                    formGroup.appendChild(label);
                    formGroup.appendChild(br);
                    
                    if (q.type === 'select' && q.options && q.options.length > 0) {
                        // FIXED: Include layer_nama_farm for dropdown search
                        if (q.field_name === 'nama_farm' || q.field_name === 'layer_nama_farm') {
                            // Create dropdown search for nama_farm and layer_nama_farm
                            var dropdown = document.createElement('div');
                            dropdown.className = 'dropdown';
                            
                            var dropbtn = document.createElement('button');
                            dropbtn.type = 'button';
                            dropbtn.className = 'dropbtn';
                            dropbtn.id = 'dropbtn_' + q.questions_id;
                            dropbtn.textContent = '-- Pilih Nama Farm --';
                            dropbtn.onclick = function() {
                                toggleDropdown('dropdown_' + q.questions_id);
                            };
                            
                            var dropdownContent = document.createElement('div');
                            dropdownContent.id = 'dropdown_' + q.questions_id;
                            dropdownContent.className = 'dropdown-content';
                            
                            var searchInput = document.createElement('input');
                            searchInput.type = 'text';
                            searchInput.placeholder = 'Search...';
                            searchInput.className = 'dropdown-search-input';
                            searchInput.onkeyup = function() {
                                filterDropdown('dropdown_' + q.questions_id);
                            };
                            searchInput.onclick = function(event) {
                                event.stopPropagation();
                            };
                            
                            dropdownContent.appendChild(searchInput);
                            
                            q.options.forEach(function(opt) {
                                var a = document.createElement('a');
                                a.textContent = opt.option_text;
                                a.onclick = function() {
                                    selectOption(q.questions_id, opt.option_text);
                                };
                                dropdownContent.appendChild(a);
                            });
                            
                            var hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = 'q' + q.questions_id;
                            hiddenInput.id = 'hidden_' + q.questions_id;
                            if (q.required) hiddenInput.required = true;
                            
                            dropdown.appendChild(dropbtn);
                            dropdown.appendChild(dropdownContent);
                            formGroup.appendChild(dropdown);
                            formGroup.appendChild(hiddenInput);
                            
                        } else {
                            // Regular select for other fields
                            var select = document.createElement('select');
                            select.name = 'q' + q.questions_id;
                            select.className = 'form-control';
                            
                            // Add special handling for tipe_ternak field
                            if (q.field_name === 'tipe_ternak') {
                                select.onchange = function() {
                                    changeTipeTermak(this.value);
                                };
                            }
                            
                            if (q.required) select.required = true;
                            
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
                        }
                        
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
