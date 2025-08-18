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
        
        /* Search functionality styles */
        .search-container {
            position: relative;
            width: 100%;
            max-width: 400px;
        }
        .search-input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: white;
        }
        .search-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 4px 4px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }
        .search-option {
            padding: 8px 12px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
        }
        .search-option:hover {
            background-color: #f8f9fa;
        }
        .search-option:last-child {
            border-bottom: none;
        }
        .search-option.selected {
            background-color: #007bff;
            color: white;
        }
        .no-results {
            padding: 8px 12px;
            color: #666;
            font-style: italic;
        }
        .hidden-select {
            display: none;
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
                $pakan_questions = [];
                $other_questions = [];
                
                foreach ($questions as $q) {
                    if (trim(strtolower($q['question_text'])) === 'jenis ternak pedaging') {
                        $jenis_ternak_q = $q;
                    } elseif ($q['field_name'] === 'nama_peternak') {
                        $nama_peternak_q = $q;
                    } elseif ($q['field_name'] === 'nama_farm') {
                        $nama_farm_q = $q;
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

                <!-- Tampilkan "Nama Farm" dengan Search Function -->
                <?php if ($nama_farm_q): ?>
                    <div class="form-group" data-field="<?= $nama_farm_q['field_name'] ?>">
                        <label>
                            <?= $nama_farm_q['question_text'] ?>
                            <?php if (!empty($nama_farm_q['required'])): ?> 
                                <span class="required">*</span> 
                            <?php endif; ?>
                        </label>
                        
                        <?php if ($nama_farm_q['type'] == 'select' && !empty($nama_farm_q['options'])): ?>
                            <!-- Search Container -->
                            <div class="search-container">
                                <input type="text" 
                                       class="search-input" 
                                       id="farmSearchInput"
                                       placeholder="Ketik untuk mencari farm..."
                                       autocomplete="off">
                                <div class="search-dropdown" id="farmDropdown">
                                    <!-- Options will be populated here -->
                                </div>
                            </div>
                            
                            <!-- Hidden select for form submission -->
                            <select name="q<?= $nama_farm_q['questions_id'] ?>" 
                                    data-field="<?= $nama_farm_q['field_name'] ?>"
                                    id="namaFarmSelect"
                                    class="hidden-select"
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
        const jenisTernakSelect = document.getElementById('jenisTernakSelect');
        const namaPeternakSelect = document.getElementById('namaPeternakSelect');
        const namaFarmSelect = document.getElementById('namaFarmSelect');
        const farmSearchInput = document.getElementById('farmSearchInput');
        const farmDropdown = document.getElementById('farmDropdown');
        
        let farmOptions = [];
        let filteredFarmOptions = [];
        let currentSelectedIndex = -1;
        
        // Initialize farm options
        if (namaFarmSelect) {
            farmOptions = Array.from(namaFarmSelect.options).slice(1).map(option => ({
                value: option.value,
                text: option.text,
                tipe: option.getAttribute('data-tipe') || '',
                element: option
            }));
        }
        
        if (jenisTernakSelect) {
            // Hide all pakan questions initially
            hidePakanQuestions();
            // Hide all options initially
            hideAllOptionsByTipe();
            
            // Add change event listener for jenis ternak
            jenisTernakSelect.addEventListener('change', function() {
                const selectedValue = this.value.toLowerCase();
                const selectedOption = this.options[this.selectedIndex];
                const selectedTipe = selectedOption.getAttribute('data-tipe') || selectedValue;
                
                // Show relevant pakan question
                showRelevantPakanQuestion(selectedValue);
                
                // Filter all options by tipe ternak
                filterOptionsByTipe(selectedTipe);
                
                // Reset dependent selects
                resetDependentSelects();
                
                // Update farm search options
                updateFarmSearchOptions(selectedTipe);
            });
        }
        
        // Farm search functionality
        if (farmSearchInput && farmDropdown) {
            farmSearchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase();
                filterFarmOptions(query);
                showFarmDropdown();
            });
            
            farmSearchInput.addEventListener('focus', function() {
                // Always show all available options when focused
                renderFarmDropdown(filteredFarmOptions);
                showFarmDropdown();
            });
            
            farmSearchInput.addEventListener('keydown', function(e) {
                switch(e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        navigateDropdown(1);
                        break;
                    case 'ArrowUp':
                        e.preventDefault();
                        navigateDropdown(-1);
                        break;
                    case 'Enter':
                        e.preventDefault();
                        selectCurrentOption();
                        break;
                    case 'Escape':
                        hideFarmDropdown();
                        break;
                }
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.search-container')) {
                    hideFarmDropdown();
                }
            });
        }
        
        function updateFarmSearchOptions(selectedTipe) {
            // Filter farm options based on selected tipe
            filteredFarmOptions = farmOptions.filter(option => {
                return !option.tipe || option.tipe === '' || 
                       option.tipe === selectedTipe || 
                       option.tipe.toLowerCase() === selectedTipe.toLowerCase();
            });
            
            // Clear search input when tipe changes
            if (farmSearchInput) {
                farmSearchInput.value = '';
            }
            
            // Reset farm select
            if (namaFarmSelect) {
                namaFarmSelect.selectedIndex = 0;
            }
            
            // Show all available options for the selected tipe immediately
            if (filteredFarmOptions.length > 0) {
                renderFarmDropdown(filteredFarmOptions);
            }
        }
        
        function filterFarmOptions(query) {
            let optionsToShow = filteredFarmOptions;
            
            if (query) {
                // Filter by query if there's input
                optionsToShow = filteredFarmOptions.filter(option =>
                    option.text.toLowerCase().includes(query)
                );
            }
            
            renderFarmDropdown(optionsToShow);
        }
        
        function renderFarmDropdown(options) {
            farmDropdown.innerHTML = '';
            currentSelectedIndex = -1;
            
            if (options.length === 0) {
                const noResultDiv = document.createElement('div');
                noResultDiv.className = 'no-results';
                noResultDiv.textContent = 'Tidak ada hasil ditemukan';
                farmDropdown.appendChild(noResultDiv);
                return;
            }
            
            options.forEach((option, index) => {
                const optionDiv = document.createElement('div');
                optionDiv.className = 'search-option';
                optionDiv.textContent = option.text;
                optionDiv.setAttribute('data-value', option.value);
                optionDiv.setAttribute('data-index', index);
                
                optionDiv.addEventListener('click', function() {
                    selectFarmOption(option.value, option.text);
                });
                
                farmDropdown.appendChild(optionDiv);
            });
        }
        
        function showFarmDropdown() {
            farmDropdown.style.display = 'block';
        }
        
        function hideFarmDropdown() {
            farmDropdown.style.display = 'none';
            currentSelectedIndex = -1;
        }
        
        function navigateDropdown(direction) {
            const options = farmDropdown.querySelectorAll('.search-option');
            if (options.length === 0) return;
            
            // Remove current selection
            if (currentSelectedIndex >= 0) {
                options[currentSelectedIndex].classList.remove('selected');
            }
            
            // Update index
            currentSelectedIndex += direction;
            
            // Wrap around
            if (currentSelectedIndex < 0) {
                currentSelectedIndex = options.length - 1;
            } else if (currentSelectedIndex >= options.length) {
                currentSelectedIndex = 0;
            }
            
            // Add selection to new option
            options[currentSelectedIndex].classList.add('selected');
            options[currentSelectedIndex].scrollIntoView({ block: 'nearest' });
        }
        
        function selectCurrentOption() {
            const selectedOption = farmDropdown.querySelector('.search-option.selected');
            if (selectedOption) {
                const value = selectedOption.getAttribute('data-value');
                const text = selectedOption.textContent;
                selectFarmOption(value, text);
            }
        }
        
        function selectFarmOption(value, text) {
            farmSearchInput.value = text;
            namaFarmSelect.value = value;
            hideFarmDropdown();
            
            // Trigger change event on the hidden select
            const changeEvent = new Event('change', { bubbles: true });
            namaFarmSelect.dispatchEvent(changeEvent);
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
        
        function hideAllOptionsByTipe() {
            // Hide all options initially
            const allOptions = document.querySelectorAll('.option-item');
            allOptions.forEach(function(option) {
                option.style.display = 'none';
            });
        }
        
        function resetDependentSelects() {
            // Reset nama peternak and nama farm selects
            if (namaPeternakSelect) {
                namaPeternakSelect.selectedIndex = 0;
            }
            if (namaFarmSelect) {
                namaFarmSelect.selectedIndex = 0;
            }
            if (farmSearchInput) {
                farmSearchInput.value = '';
            }
        }
        
        function filterOptionsByTipe(selectedTipe) {
            if (!selectedTipe) {
                // If no tipe selected, hide all options
                hideAllOptionsByTipe();
                return;
            }
            
            // Show/hide options based on tipe ternak
            const allOptions = document.querySelectorAll('.option-item');
            allOptions.forEach(function(option) {
                const optionTipe = option.getAttribute('data-tipe') || option.dataset.tipe;
                
                // Show if tipe matches or if option has no specific tipe (global options)
                if (!optionTipe || optionTipe === '' || optionTipe === selectedTipe || 
                    optionTipe.toLowerCase() === selectedTipe.toLowerCase()) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            });
            
            // Reset selects that have filtered options
            const allSelects = document.querySelectorAll('select:not(#jenisTernakSelect)');
            allSelects.forEach(function(select) {
                // Skip the hidden farm select as it's handled by search
                if (select.id === 'namaFarmSelect') return;
                
                // Reset to default option
                select.selectedIndex = 0;
                
                // Hide/show option elements in select
                const selectOptions = select.querySelectorAll('option.option-item');
                selectOptions.forEach(function(option) {
                    const optionTipe = option.getAttribute('data-tipe') || option.dataset.tipe;
                    
                    if (!optionTipe || optionTipe === '' || optionTipe === selectedTipe || 
                        optionTipe.toLowerCase() === selectedTipe.toLowerCase()) {
                        option.style.display = 'block';
                        option.disabled = false;
                    } else {
                        option.style.display = 'none';
                        option.disabled = true;
                    }
                });
            });
        }
    });
    </script>
</body>
</html>
