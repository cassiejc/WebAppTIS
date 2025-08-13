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
    <h2>Form Pedaging - Area 1.1</h2>

    <form method="post" action="" id="pulletForm">
        <div id="pulletQuestions">
            <!-- Jenis Ternak Pedaging -->
            <div class="form-group">
                <label>
                    Jenis Ternak Pedaging
                    <span class="required">*</span> 
                </label>
                <select name="jenis_ternak" id="jenisTernakSelect" data-field="jenis_ternak" required>
                    <option value="">-- Pilih Jawaban --</option>
                    <option value="pullet" data-tipe="pullet">Pullet</option>
                    <option value="bebek_pedaging" data-tipe="bebek_pedaging">Bebek Pedaging</option>
                </select>
            </div>

            <!-- Nama Peternak -->
            <div class="form-group" data-field="nama_peternak">
                <label>
                    Nama Peternak
                    <span class="required">*</span> 
                </label>
                <select name="nama_peternak" data-field="nama_peternak" id="namaPeternakSelect" required>
                    <option value="">-- Pilih Jawaban --</option>
                    <option value="Peternak ABC" data-tipe="pullet" data-nama-peternak="Peternak ABC" class="option-item">Peternak ABC</option>
                    <option value="Peternak C" data-tipe="pullet" data-nama-peternak="Peternak C" class="option-item">Peternak C</option>
                    <option value="Peternak XYZ" data-tipe="bebek_pedaging" data-nama-peternak="Peternak XYZ" class="option-item">Peternak XYZ</option>
                </select>
            </div>

            <!-- Nama Farm -->
            <div class="form-group" data-field="nama_farm">
                <label>
                    Nama Farm
                    <span class="required">*</span> 
                </label>
                <select name="nama_farm" data-field="nama_farm" id="namaFarmSelect" required>
                    <option value="">-- Pilih Jawaban --</option>
                    <option value="Farm A" data-tipe="pullet" data-nama-peternak="Peternak ABC" class="option-item">Farm A</option>
                    <option value="Farm Z" data-tipe="pullet" data-nama-peternak="Peternak ABC" class="option-item">Farm Z</option>
                    <option value="Farm E" data-tipe="pullet" data-nama-peternak="Peternak C" class="option-item">Farm E</option>
                    <option value="Farm F" data-tipe="bebek_pedaging" data-nama-peternak="Peternak C" class="option-item">Farm F</option>
                    <option value="Sinar Farm" data-tipe="bebek_pedaging" data-nama-peternak="Peternak XYZ" class="option-item">Sinar Farm</option>
                </select>
            </div>

            <!-- Pakan Questions (hidden initially) -->
            <div class="form-group pakan-question" data-field="pakan_pedaging_pullet" id="pakan_pakan_pedaging_pullet">
                <label>
                    Pakan Pedaging Pullet
                    <span class="required">*</span> 
                </label>
                <div class="radio-group">
                    <label class="option-item">
                        <input type="radio" name="pakan_pedaging_pullet" value="BR-1" data-field="pakan_pedaging_pullet"> 
                        BR-1
                    </label>
                    <label class="option-item">
                        <input type="radio" name="pakan_pedaging_pullet" value="BR-2" data-field="pakan_pedaging_pullet"> 
                        BR-2
                    </label>
                </div>
            </div>

            <div class="form-group pakan-question" data-field="pakan_pedaging_bebek" id="pakan_pakan_pedaging_bebek">
                <label>
                    Pakan Pedaging Bebek
                    <span class="required">*</span> 
                </label>
                <div class="radio-group">
                    <label class="option-item">
                        <input type="radio" name="pakan_pedaging_bebek" value="Bebek Starter" data-field="pakan_pedaging_bebek"> 
                        Bebek Starter
                    </label>
                    <label class="option-item">
                        <input type="radio" name="pakan_pedaging_bebek" value="Bebek Grower" data-field="pakan_pedaging_bebek"> 
                        Bebek Grower
                    </label>
                </div>
            </div>

            <!-- Other Questions -->
            <div class="form-group">
                <label>
                    Tanggal Chick In
                    <span class="required">*</span> 
                </label>
                <input type="date" name="tanggal_chick_in" data-field="tanggal_chick_in" required>
            </div>

            <div class="form-group">
                <label>
                    Strain Yang Digunakan
                    <span class="required">*</span> 
                </label>
                <input type="text" name="strain_yang_digunakan" data-field="strain_yang_digunakan" placeholder="Masukkan jawaban Anda" required>
            </div>

            <div class="form-group">
                <label>
                    Deplesi (%)
                    <span class="required">*</span> 
                </label>
                <textarea name="deplesi" data-field="deplesi" placeholder="Masukkan jawaban Anda" required></textarea>
            </div>
        </div>
        
        <button type="submit" class="btn-submit" id="submitBtn">Submit</button>
    </form>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const jenisTernakSelect = document.getElementById('jenisTernakSelect');
        const namaPeternakSelect = document.getElementById('namaPeternakSelect');
        const namaFarmSelect = document.getElementById('namaFarmSelect');
        
        if (jenisTernakSelect) {
            // Hide all pakan questions initially
            hidePakanQuestions();
            // Hide all options initially
            hideAllOptionsByFilters();
            
            // Add change event listener for jenis ternak
            jenisTernakSelect.addEventListener('change', function() {
                const selectedValue = this.value.toLowerCase();
                const selectedOption = this.options[this.selectedIndex];
                const selectedTipe = selectedOption.getAttribute('data-tipe') || selectedValue;
                
                console.log('Jenis Ternak changed:', selectedTipe);
                
                // Show relevant pakan question
                showRelevantPakanQuestion(selectedValue);
                
                // Filter nama peternak options by tipe ternak
                filterNamaPeternakByTipe(selectedTipe);
                
                // Reset dependent selects
                resetDependentSelects();
            });
        }
        
        if (namaPeternakSelect) {
            // Add change event listener for nama peternak
            namaPeternakSelect.addEventListener('change', function() {
                const selectedNamaPeternak = this.value;
                const jenisTernak = jenisTernakSelect.value;
                const selectedOption = jenisTernakSelect.options[jenisTernakSelect.selectedIndex];
                const selectedTipe = selectedOption.getAttribute('data-tipe') || jenisTernak;
                
                console.log('Nama Peternak changed:', selectedNamaPeternak);
                console.log('Current Tipe Ternak:', selectedTipe);
                
                // Filter nama farm by both tipe ternak AND nama peternak
                filterNamaFarmByBothFilters(selectedTipe, selectedNamaPeternak);
                
                // Reset nama farm select
                if (namaFarmSelect) {
                    namaFarmSelect.selectedIndex = 0;
                }
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
                    
                    // Add back required attribute
                    const requiredInputs = targetQuestion.querySelectorAll('input[type="radio"]');
                    requiredInputs.forEach(function(input) {
                        input.setAttribute('required', 'required');
                    });
                }
            }
        }
        
        function hideAllOptionsByFilters() {
            // Hide all filterable options initially
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
        }
        
        function filterNamaPeternakByTipe(selectedTipe) {
            if (!selectedTipe) {
                // If no tipe selected, hide all options
                hideAllOptionsByFilters();
                return;
            }
            
            console.log('Filtering Nama Peternak by tipe:', selectedTipe);
            
            // Show/hide nama peternak options based on tipe ternak only
            const namaPeternakOptions = namaPeternakSelect.querySelectorAll('.option-item');
            namaPeternakOptions.forEach(function(option) {
                const optionTipe = option.getAttribute('data-tipe');
                
                console.log('Option tipe:', optionTipe, 'Selected tipe:', selectedTipe);
                
                if (!optionTipe || optionTipe === selectedTipe) {
                    option.style.display = 'block';
                    option.disabled = false;
                } else {
                    option.style.display = 'none';
                    option.disabled = true;
                }
            });
            
            // Also hide all nama farm options until nama peternak is selected
            const namaFarmOptions = namaFarmSelect.querySelectorAll('.option-item');
            namaFarmOptions.forEach(function(option) {
                option.style.display = 'none';
                option.disabled = true;
            });
        }
        
        function filterNamaFarmByBothFilters(selectedTipe, selectedNamaPeternak) {
            if (!selectedTipe || !selectedNamaPeternak) {
                // If either filter is empty, hide all nama farm options
                const namaFarmOptions = namaFarmSelect.querySelectorAll('.option-item');
                namaFarmOptions.forEach(function(option) {
                    option.style.display = 'none';
                    option.disabled = true;
                });
                return;
            }
            
            console.log('Filtering Nama Farm by tipe:', selectedTipe, 'and nama peternak:', selectedNamaPeternak);
            
            // Show/hide nama farm options based on BOTH tipe ternak AND nama peternak
            const namaFarmOptions = namaFarmSelect.querySelectorAll('.option-item');
            namaFarmOptions.forEach(function(option) {
                const optionTipe = option.getAttribute('data-tipe');
                const optionNamaPeternak = option.getAttribute('data-nama-peternak');
                
                console.log('Farm option - tipe:', optionTipe, 'nama peternak:', optionNamaPeternak);
                
                // Show only if BOTH tipe ternak AND nama peternak match
                if (optionTipe === selectedTipe && optionNamaPeternak === selectedNamaPeternak) {
                    option.style.display = 'block';
                    option.disabled = false;
                    console.log('Showing farm:', option.value);
                } else {
                    option.style.display = 'none';
                    option.disabled = true;
                    console.log('Hiding farm:', option.value);
                }
            });
        }
    });
    </script>
</body>
</html>
