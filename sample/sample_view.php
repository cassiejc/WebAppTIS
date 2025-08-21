<!DOCTYPE html>
<html>
<head>
    <title>Form Sample</title>
    <style>
        form {
            margin-left: 20px;
        }
        h2 {
            margin-left: 10px;
        }
        
        /* Custom dropdown styles for nama farm filter */
        .custom-dropdown {
            position: relative;
            display: block;
            width: 100%;
            max-width: 400px;
            margin-top: 5px;
        }
        
        .dropdown-toggle {
            background-color: white;
            color: #333;
            padding: 8px 12px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            text-align: left;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-sizing: border-box;
        }
        
        .dropdown-toggle:hover, .dropdown-toggle:focus {
            background-color: #f8f9fa;
            border-color: #007bff;
        }
        
        .dropdown-toggle::after {
            content: "▼";
            font-size: 12px;
        }
        
        .farm-search-input {
            box-sizing: border-box;
            font-size: 14px;
            padding: 8px 12px;
            border: none;
            border-bottom: 1px solid #ddd;
            width: 100%;
            background-color: #f8f9fa;
        }
        
        .farm-search-input:focus {
            outline: 2px solid #007bff;
            background-color: white;
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: white;
            min-width: 100%;
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 0 0 4px 4px;
            border-top: none;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .dropdown-content .farm-option {
            color: #333;
            padding: 10px 12px;
            text-decoration: none;
            display: block;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        
        .dropdown-content .farm-option:hover {
            background-color: #f8f9fa;
        }
        
        .dropdown-content .farm-option:last-child {
            border-bottom: none;
        }
        
        .show {
            display: block;
        }

        .selected-farm {
            background-color: #e7f3ff;
            border-color: #007bff;
        }

        /* Other input styles */
        .other-input {
            margin-top: 10px;
        }

        .other-input input {
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
    </style>
    <script>
        function toggleOtherInput(questionId, optionText) {
            var otherInput = document.getElementById('other_input_' + questionId);
            var otherTextField = otherInput.querySelector('input');
            if (optionText.toLowerCase() === 'other') {
                otherInput.style.display = 'block';
                otherTextField.required = true;
            } else {
                otherInput.style.display = 'none';
                otherTextField.value = '';
                otherTextField.required = false;
            }
        }

        function validateForm() {
            var radioGroups = document.querySelectorAll('input[type="radio"]');
            var radioGroupsChecked = {};
            
            // Check which radio buttons are selected
            radioGroups.forEach(function(radio) {
                var name = radio.name;
                if (radio.checked) {
                    radioGroupsChecked[name] = radio.value;
                }
            });

            // Check select elements
            var selects = document.querySelectorAll('select');
            selects.forEach(function(select) {
                var name = select.name;
                if (select.value) {
                    radioGroupsChecked[name] = select.value;
                }
            });

            // Check custom dropdowns
            var customDropdowns = document.querySelectorAll('input[data-custom-dropdown="true"]');
            customDropdowns.forEach(function(dropdown) {
                var name = dropdown.name;
                if (dropdown.value) {
                    radioGroupsChecked[name] = dropdown.value;
                }
            });

            // Validate "Other" fields
            for (var questionName in radioGroupsChecked) {
                if (radioGroupsChecked[questionName].toLowerCase() === 'other') {
                    var questionId = questionName.replace('q', '');
                    var otherField = document.querySelector('input[name="' + questionName + '_other"]');
                    if (!otherField.value.trim()) {
                        alert('Please specify the "Other" option for question ' + questionId);
                        otherField.focus();
                        return false;
                    }
                }
            }
            return true;
        }

        // Farm dropdown functions
        function toggleFarmDropdown(questionId) {
            const dropdown = document.getElementById('farmDropdown_' + questionId);
            dropdown.classList.toggle('show');
            
            if (dropdown.classList.contains('show')) {
                // Focus on search input when dropdown opens
                setTimeout(() => {
                    document.getElementById('farmSearchInput_' + questionId).focus();
                }, 100);
            }
        }

        function closeFarmDropdown(questionId) {
            document.getElementById('farmDropdown_' + questionId).classList.remove('show');
        }

        function selectFarmOption(questionId, farmName) {
            // Set the selected value
            document.getElementById('farmHidden_' + questionId).value = farmName;
            document.getElementById('selectedFarmText_' + questionId).textContent = farmName;
            document.getElementById('farmToggle_' + questionId).classList.add('selected-farm');
            
            // Close dropdown
            closeFarmDropdown(questionId);
            
            // Clear search input
            document.getElementById('farmSearchInput_' + questionId).value = '';
            
            // Show all options again
            const allFarmOptions = document.querySelectorAll('#farmDropdown_' + questionId + ' .farm-option');
            allFarmOptions.forEach(function(option) {
                option.style.display = 'block';
            });

            // Check if selected option is "Other" and toggle other input
            toggleOtherInput(questionId, farmName);
        }

        function filterFarmOptions(questionId) {
            const input = document.getElementById('farmSearchInput_' + questionId);
            const filter = input.value.toUpperCase();
            const options = document.querySelectorAll('#farmDropdown_' + questionId + ' .farm-option');
            
            options.forEach(function(option) {
                const txtValue = option.textContent || option.innerText;
                
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            });
        }

        // Close dropdown when clicking outside
        document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('click', function(event) {
                if (!event.target.closest('.custom-dropdown')) {
                    // Close all farm dropdowns
                    const allDropdowns = document.querySelectorAll('.dropdown-content');
                    allDropdowns.forEach(function(dropdown) {
                        dropdown.classList.remove('show');
                    });
                }
            });
        });
    </script>
</head>
<body>
    <h2>Form Sample</h2>

    <form method="post" action="" onsubmit="return validateForm()">
        <?php if (!empty($questions)): ?>
            <?php
            // Separate questions by field names for custom ordering
            $ordered_questions = [];
            $other_questions = [];
            
            foreach ($questions as $q) {
                if ($q['field_name'] === 'jenis_ternak_sample') {
                    $ordered_questions['jenis_ternak'] = $q;
                } elseif ($q['field_name'] === 'nama_farm') {
                    $ordered_questions['nama_farm'] = $q;
                } else {
                    $other_questions[] = $q;
                }
            }
            
            // Create final ordered array
            $final_questions = [];
            if (isset($ordered_questions['jenis_ternak'])) {
                $final_questions[] = $ordered_questions['jenis_ternak'];
            }
            if (isset($ordered_questions['nama_farm'])) {
                $final_questions[] = $ordered_questions['nama_farm'];
            }
            $final_questions = array_merge($final_questions, $other_questions);
            ?>
            
            <?php foreach ($final_questions as $q): ?>
                <div style="margin-bottom: 20px;">
                    <label>
                        <?= $q['question_text'] ?>
                        <?php if (!empty($q['required'])): ?> <span style="color: red">*</span> <?php endif; ?>
                    </label>
                    <br>

                    <?php if ($q['type'] == 'radio' && !empty($q['options'])): ?>
                        <?php foreach ($q['options'] as $opt): ?>
                            <input type="radio" name="q<?= $q['questions_id'] ?>" value="<?= $opt['option_text'] ?>" <?= !empty($q['required']) ? 'required' : '' ?> onchange="toggleOtherInput(<?= $q['questions_id'] ?>, '<?= $opt['option_text'] ?>')"> <?= $opt['option_text'] ?><br>
                        <?php endforeach; ?>
                        <div id="other_input_<?= $q['questions_id'] ?>" style="display: none;" class="other-input">
                            <input type="text" name="q<?= $q['questions_id'] ?>_other" placeholder="Please specify...">
                        </div>

                    <?php elseif ($q['type'] == 'select' && !empty($q['options']) && $q['field_name'] === 'nama_farm'): ?>
                        <!-- Custom searchable dropdown for nama_farm -->
                        <div class="custom-dropdown">
                            <!-- Hidden input to store the actual value for form submission -->
                            <input type="hidden" 
                                   name="q<?= $q['questions_id'] ?>" 
                                   id="farmHidden_<?= $q['questions_id'] ?>"
                                   data-custom-dropdown="true"
                                   <?= !empty($q['required']) ? 'required' : '' ?>>
                            
                            <!-- Display button -->
                            <button type="button" onclick="toggleFarmDropdown(<?= $q['questions_id'] ?>)" class="dropdown-toggle" id="farmToggle_<?= $q['questions_id'] ?>">
                                <span id="selectedFarmText_<?= $q['questions_id'] ?>">-- Pilih --</span>
                            </button>
                            
                            <!-- Dropdown content -->
                            <div id="farmDropdown_<?= $q['questions_id'] ?>" class="dropdown-content">
                                <input type="text" 
                                       placeholder="Search farm..." 
                                       id="farmSearchInput_<?= $q['questions_id'] ?>" 
                                       class="farm-search-input"
                                       onkeyup="filterFarmOptions(<?= $q['questions_id'] ?>)">
                                
                                <?php foreach ($q['options'] as $opt): ?>
                                    <div class="farm-option" 
                                         data-value="<?= $opt['option_text'] ?>"
                                         onclick="selectFarmOption(<?= $q['questions_id'] ?>, '<?= $opt['option_text'] ?>')">
                                        <?= $opt['option_text'] ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div id="other_input_<?= $q['questions_id'] ?>" style="display: none;" class="other-input">
                            <input type="text" name="q<?= $q['questions_id'] ?>_other" placeholder="Please specify...">
                        </div>

                    <?php elseif ($q['type'] == 'select' && !empty($q['options'])): ?>
                        <!-- Regular select dropdown for other fields -->
                        <select name="q<?= $q['questions_id'] ?>" <?= !empty($q['required']) ? 'required' : '' ?> onchange="toggleOtherInput(<?= $q['questions_id'] ?>, this.value)">
                            <option value="">-- Pilih --</option>
                            <?php foreach ($q['options'] as $opt): ?>
                                <option value="<?= $opt['option_text'] ?>"><?= $opt['option_text'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div id="other_input_<?= $q['questions_id'] ?>" style="display: none;" class="other-input">
                            <input type="text" name="q<?= $q['questions_id'] ?>_other" placeholder="Please specify...">
                        </div>

                    <?php elseif ($q['type'] == 'text'): ?>
                        <input type="text" name="q<?= $q['questions_id'] ?>" <?= !empty($q['required']) ? 'required' : '' ?>><br>

                    <?php elseif ($q['type'] == 'date'): ?>
                        <input type="date" name="q<?= $q['questions_id'] ?>" <?= !empty($q['required']) ? 'required' : '' ?>><br>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <input type="submit" value="Submit" style="margin-bottom: 20px;">
        <?php else: ?>
            <p>Tidak ada pertanyaan.</p>
        <?php endif; ?>
    </form>
</body>
</html>
