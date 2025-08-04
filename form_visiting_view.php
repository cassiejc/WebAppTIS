<!DOCTYPE html>
<html>
<head>
    <title>Form Visiting</title>
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
            margin-top: 5px; 
        }
        .radio-group label, .checkbox-group label { 
            font-weight: normal; 
            margin-left: 5px; 
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
        
        /* Hide jenis kasus dropdown fields by default */
        .jenis-kasus-dropdown {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            height: 0 !important;
            overflow: hidden !important;
            margin: 0 !important;
            padding: 0 !important;
        }
    </style>
    <script>
        // Immediate hiding of jenis kasus dropdown fields
        (function() {
            function hideJenisKasusFieldsImmediately() {
                const allFormGroups = document.querySelectorAll('.form-group');
                allFormGroups.forEach(function(group) {
                    const label = group.querySelector('label');
                    if (label) {
                        const labelText = label.textContent.toLowerCase();
                        if ((labelText.includes('bacterial') || labelText.includes('virus') || 
                            labelText.includes('parasit') || labelText.includes('jamur') || 
                            labelText.includes('lain-lain') || labelText.includes('lain_lain')) && 
                            !labelText.includes('jenis kasus')) {
                            
                            group.classList.add('jenis-kasus-dropdown');
                            group.style.display = 'none';
                            group.style.visibility = 'hidden';
                            group.style.opacity = '0';
                            group.style.height = '0';
                            group.style.overflow = 'hidden';
                            group.style.margin = '0';
                            group.style.padding = '0';
                            
                            // Disable all inputs
                            const inputs = group.querySelectorAll('input, select, textarea');
                            inputs.forEach(function(input) {
                                input.disabled = true;
                                input.removeAttribute('required');
                                if (input.type === 'radio' || input.type === 'checkbox') {
                                    input.checked = false;
                                } else {
                                    input.value = '';
                                }
                            });
                        }
                    }
                });
            }
            
            // Run immediately
            hideJenisKasusFieldsImmediately();
            
            // Run again when DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', hideJenisKasusFieldsImmediately);
            } else {
                hideJenisKasusFieldsImmediately();
            }
            
            // Run again after a short delay
            setTimeout(hideJenisKasusFieldsImmediately, 10);
            setTimeout(hideJenisKasusFieldsImmediately, 50);
            setTimeout(hideJenisKasusFieldsImmediately, 100);
            setTimeout(hideJenisKasusFieldsImmediately, 200);
            setTimeout(hideJenisKasusFieldsImmediately, 500);
        })();
    </script>
</head>
<body>
    <h2>Form Visiting - <?php echo $current_sub_area['nama_sub_area']; ?></h2>

    <form method="post" action="" id="visitingForm">
        <input type="hidden" name="action" value="next">
        <div id="initialQuestions">
            <?php if (!empty($questions)): ?>
                <?php foreach ($questions as $q): ?>
                    <div class="form-group">
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
                                               <?= !empty($q['required']) ? 'required' : '' ?>
                                               <?php if ($q['field_name'] == 'kunjungan_ke'): ?>onchange="loadSpecificQuestions('<?= $opt['option_text'] ?>')"<?php endif; ?>
                                               <?php if ($q['field_name'] == 'tujuan_kunjungan'): ?>onchange="toggleJenisKasus(this.value)"<?php endif; ?>
                                               <?php if ($q['field_name'] == 'jenis_kasus'): ?>onchange="toggleJenisKasusFields(this.value)"<?php endif; ?>
                                               > 
                                        <?= $opt['option_text'] ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php elseif ($q['type'] == 'text'): ?>
                            <input type="text" 
                                   name="q<?= $q['questions_id'] ?>" 
                                   placeholder="Masukkan jawaban Anda"
                                   <?= !empty($q['required']) ? 'required' : '' ?>>
                        <?php elseif ($q['type'] == 'textarea'): ?>
                            <textarea name="q<?= $q['questions_id'] ?>" 
                                      placeholder="Masukkan jawaban Anda"
                                      <?= !empty($q['required']) ? 'required' : '' ?>></textarea>
                                                 <?php elseif ($q['type'] == 'select' && !empty($q['options'])): ?>
                             <select name="q<?= $q['questions_id'] ?>" 
                                     <?= !empty($q['required']) ? 'required' : '' ?>
                                     <?php if ($q['field_name'] == 'kunjungan_ke'): ?>onchange="loadSpecificQuestions(this.value)"<?php endif; ?>>
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
                                   <?= !empty($q['required']) ? 'required' : '' ?>>
                        <?php elseif ($q['type'] == 'checkbox' && !empty($q['options'])): ?>
                            <div class="checkbox-group">
                                <?php foreach ($q['options'] as $opt): ?>
                                    <label>
                                        <input type="checkbox" 
                                               name="q<?= $q['questions_id'] ?>[]" 
                                               value="<?= $opt['option_text'] ?>"> 
                                        <?= $opt['option_text'] ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-questions">Tidak ada pertanyaan untuk form visiting.</p>
            <?php endif; ?>
        </div>
        
        <div id="dynamicQuestions" style="display: none;">
            <h3 id="dynamicTitle"></h3>
            <div id="dynamicFormContent"></div>
        </div>
        
        <button type="submit" class="btn-submit" id="submitBtn" data-visiting-type="">Next</button>
    </form>

         <script>
         function loadSpecificQuestions(visitingType) {
             // Check if visitingType is empty or null
             if (!visitingType || visitingType === '') {
                 document.getElementById('dynamicQuestions').style.display = 'none';
                 updateButtonText('');
                 return;
             }
             
             // Update button text based on visiting type
             updateButtonText(visitingType);
             
             // Show loading indicator
             document.getElementById('dynamicQuestions').style.display = 'block';
             document.getElementById('dynamicTitle').innerHTML = 'Memuat pertanyaan untuk ' + visitingType + '...';
             document.getElementById('dynamicFormContent').innerHTML = '<p>Loading...</p>';
            
            // Make AJAX request to load specific questions
            fetch('<?= base_url("Visiting_Controller/load_form_questions") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'visiting_type=' + encodeURIComponent(visitingType)
            })
            .then(response => response.json())
                         .then(data => {
                 console.log('Response data:', data); // Debug log
                 if (data.questions && data.questions.length > 0) {
                     let formContent = '';
                     data.questions.forEach(function(q) {
                         console.log('Question:', q); // Debug log
                         formContent += '<div class="form-group">';
                         formContent += '<label>';
                         formContent += q.question_text;
                         if (q.required) {
                             formContent += ' <span class="required">*</span>';
                         }
                         formContent += '</label>';
                        
                                                 if (q.type === 'radio' && q.options && q.options.length > 0) {
                             formContent += '<div class="radio-group">';
                             q.options.forEach(function(opt) {
                                 formContent += '<label>';
                                 formContent += '<input type="radio" name="q' + q.questions_id + '" value="' + opt.option_text + '"';
                                 if (q.required) {
                                     formContent += ' required';
                                 }
                                 // Add event listener for tujuan kunjungan in dynamic content
                                 if (q.field_name === 'tujuan_kunjungan') {
                                     formContent += ' onchange="toggleJenisKasus(this.value)"';
                                 }
                                 // Add event listener for jenis kasus in dynamic content
                                 if (q.field_name === 'jenis_kasus') {
                                     formContent += ' onchange="toggleJenisKasusFields(this.value)"';
                                 }
                                 formContent += '> ' + opt.option_text;
                                 formContent += '</label>';
                             });
                             formContent += '</div>';
                        } else if (q.type === 'text') {
                            formContent += '<input type="text" name="q' + q.questions_id + '" placeholder="Masukkan jawaban Anda"';
                            if (q.required) {
                                formContent += ' required';
                            }
                            formContent += '>';
                        } else if (q.type === 'textarea') {
                            formContent += '<textarea name="q' + q.questions_id + '" placeholder="Masukkan jawaban Anda"';
                            if (q.required) {
                                formContent += ' required';
                            }
                            formContent += '></textarea>';
                        } else if (q.type === 'select' && q.options && q.options.length > 0) {
                            formContent += '<select name="q' + q.questions_id + '"';
                            if (q.required) {
                                formContent += ' required';
                            }
                            formContent += '>';
                            formContent += '<option value="">-- Pilih Jawaban --</option>';
                            q.options.forEach(function(opt) {
                                formContent += '<option value="' + opt.option_text + '">' + opt.option_text + '</option>';
                            });
                            formContent += '</select>';
                        } else if (q.type === 'date') {
                            formContent += '<input type="date" name="q' + q.questions_id + '"';
                            if (q.required) {
                                formContent += ' required';
                            }
                            formContent += '>';
                                                 } else if (q.type === 'checkbox' && q.options && q.options.length > 0) {
                             formContent += '<div class="checkbox-group">';
                             q.options.forEach(function(opt) {
                                 formContent += '<label>';
                                 formContent += '<input type="checkbox" name="q' + q.questions_id + '[]" value="' + opt.option_text + '"> ' + opt.option_text;
                                 formContent += '</label>';
                             });
                             formContent += '</div>';
                        }
                        formContent += '</div>';
                    });
                    
                    // Add conditional display logic for Kemitraan
                    if (visitingType === 'Kemitraan') {
                        addConditionalDisplayLogic();
                    }
                    
                    // Add conditional display logic for Tujuan Kunjungan and Jenis Kasus
                    addTujuanKunjunganLogic();
                    
                    // Add conditional display logic for Jenis Kasus Fields
                    addJenisKasusFieldsLogic();
                    
                    // Hide all jenis kasus dropdown fields in dynamic content on load
                    hideJenisKasusDropdownFields();
                    forceHideJenisKasusFields();
                    
                    document.getElementById('dynamicTitle').innerHTML = 'Pertanyaan untuk ' + visitingType;
                    document.getElementById('dynamicFormContent').innerHTML = formContent;
                    
                    
                } else {
                    document.getElementById('dynamicTitle').innerHTML = 'Tidak ada pertanyaan untuk ' + visitingType;
                    document.getElementById('dynamicFormContent').innerHTML = '<p class="no-questions">Tidak ada pertanyaan tambahan untuk jenis kunjungan ini.</p>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('dynamicTitle').innerHTML = 'Error';
                document.getElementById('dynamicFormContent').innerHTML = '<p class="no-questions">Terjadi kesalahan saat memuat pertanyaan.</p>';
            });
        }
        
                 function updateButtonText(visitingType) {
             const submitBtn = document.getElementById('submitBtn');
             const actionInput = document.querySelector('input[name="action"]');
             
             if (visitingType === 'Peternak') {
                 submitBtn.textContent = 'Next';
                 actionInput.value = 'next';
             } else if (visitingType && visitingType !== '') {
                 submitBtn.textContent = 'Submit';
                 actionInput.value = 'submit';
             } else {
                 submitBtn.textContent = 'Next';
                 actionInput.value = 'next';
             }
         }
         
         function toggleJenisKasus(tujuanKunjungan) {
               console.log('Tujuan kunjungan selected:', tujuanKunjungan);
               
               // Find all form groups in the initial questions
               const formGroups = document.querySelectorAll('#initialQuestions .form-group');
               let jenisKasusGroup = null;
               
               // Find the "Jenis Kasus" question group
               formGroups.forEach(function(group) {
                   const label = group.querySelector('label');
                   if (label && (label.textContent.includes('Jenis Kasus') || label.textContent.includes('jenis kasus'))) {
                       jenisKasusGroup = group;
                   }
               });
               
               if (jenisKasusGroup) {
                   if (tujuanKunjungan === 'Monitoring') {
                       // Hide Jenis Kasus question when Monitoring is selected
                       jenisKasusGroup.style.display = 'none';
                       console.log('Hiding Jenis Kasus field for Monitoring');
                       
                       // Clear the value and remove required attribute when hiding
                       const inputs = jenisKasusGroup.querySelectorAll('input, select');
                       inputs.forEach(function(input) {
                           if (input.type === 'radio' || input.type === 'checkbox') {
                               input.checked = false;
                           } else {
                               input.value = '';
                           }
                           // Remove required attribute
                           input.removeAttribute('required');
                       });
                       
                       // Also hide all jenis kasus dropdown fields when Monitoring is selected
                       hideAllJenisKasusFields();
                   } else if (tujuanKunjungan === 'Kasus') {
                       // Show Jenis Kasus question when Kasus is selected
                       jenisKasusGroup.style.display = 'block';
                       console.log('Showing Jenis Kasus field for Kasus');
                       
                       // Add required attribute back if the field was originally required
                       const inputs = jenisKasusGroup.querySelectorAll('input, select');
                       inputs.forEach(function(input) {
                           // Check if the original field was required by looking at the label
                           const label = jenisKasusGroup.querySelector('label');
                           if (label && label.innerHTML.includes('<span class="required">*</span>')) {
                               input.setAttribute('required', 'required');
                           }
                       });
                   }
               } else {
                   console.log('Jenis Kasus field not found in initial questions');
               }
               
               // Also check in dynamic questions if they exist
               const dynamicFormGroups = document.querySelectorAll('#dynamicFormContent .form-group');
               let dynamicJenisKasusGroup = null;
               
               dynamicFormGroups.forEach(function(group) {
                   const label = group.querySelector('label');
                   if (label && (label.textContent.includes('Jenis Kasus') || label.textContent.includes('jenis kasus'))) {
                       dynamicJenisKasusGroup = group;
                   }
               });
               
               if (dynamicJenisKasusGroup) {
                   if (tujuanKunjungan === 'Monitoring') {
                       // Hide Jenis Kasus question when Monitoring is selected
                       dynamicJenisKasusGroup.style.display = 'none';
                       console.log('Hiding Jenis Kasus field in dynamic content for Monitoring');
                       
                       // Clear the value and remove required attribute when hiding
                       const inputs = dynamicJenisKasusGroup.querySelectorAll('input, select');
                       inputs.forEach(function(input) {
                           if (input.type === 'radio' || input.type === 'checkbox') {
                               input.checked = false;
                           } else {
                               input.value = '';
                           }
                           // Remove required attribute
                           input.removeAttribute('required');
                       });
                       
                       // Also hide all jenis kasus dropdown fields in dynamic content when Monitoring is selected
                       hideAllJenisKasusFields();
                   } else if (tujuanKunjungan === 'Kasus') {
                       // Show Jenis Kasus question when Kasus is selected
                       dynamicJenisKasusGroup.style.display = 'block';
                       console.log('Showing Jenis Kasus field in dynamic content for Kasus');
                       
                       // Add required attribute back if the field was originally required
                       const inputs = dynamicJenisKasusGroup.querySelectorAll('input, select');
                       inputs.forEach(function(input) {
                           // Check if the original field was required by looking at the label
                           const label = dynamicJenisKasusGroup.querySelector('label');
                           if (label && label.innerHTML.includes('<span class="required">*</span>')) {
                               input.setAttribute('required', 'required');
                           }
                       });
                   }
               }
          }
           
           function hideAllJenisKasusFields() {
                // Find all form groups in the initial questions
                const formGroups = document.querySelectorAll('#initialQuestions .form-group');
                
                // Hide all jenis kasus related fields
                formGroups.forEach(function(group) {
                    const label = group.querySelector('label');
                    if (label) {
                        const labelText = label.textContent.toLowerCase();
                        // Check for various possible field names
                        if (labelText.includes('bacterial') || labelText.includes('virus') || 
                            labelText.includes('parasit') || labelText.includes('jamur') || 
                            labelText.includes('lain-lain') || labelText.includes('lain_lain') ||
                            labelText.includes('lambat puncak')) {
                            
                            group.classList.add('jenis-kasus-dropdown');
                            group.style.display = 'none';
                            group.style.visibility = 'hidden';
                            group.style.opacity = '0';
                            group.style.height = '0';
                            group.style.overflow = 'hidden';
                            group.style.margin = '0';
                            group.style.padding = '0';
                            
                            // Clear values and remove required
                            const inputs = group.querySelectorAll('input, select');
                            inputs.forEach(function(input) {
                                input.disabled = true;
                                if (input.type === 'radio' || input.type === 'checkbox') {
                                    input.checked = false;
                                } else {
                                    input.value = '';
                                }
                                input.removeAttribute('required');
                            });
                        }
                    }
                });
                
                // Also check in dynamic questions if they exist
                const dynamicFormGroups = document.querySelectorAll('#dynamicFormContent .form-group');
                
                // Hide all jenis kasus related fields in dynamic content
                dynamicFormGroups.forEach(function(group) {
                    const label = group.querySelector('label');
                    if (label) {
                        const labelText = label.textContent.toLowerCase();
                        // Check for various possible field names
                        if (labelText.includes('bacterial') || labelText.includes('virus') || 
                            labelText.includes('parasit') || labelText.includes('jamur') || 
                            labelText.includes('lain-lain') || labelText.includes('lain_lain') ||
                            labelText.includes('lambat puncak')) {
                            
                            group.classList.add('jenis-kasus-dropdown');
                            group.style.display = 'none';
                            group.style.visibility = 'hidden';
                            group.style.opacity = '0';
                            group.style.height = '0';
                            group.style.overflow = 'hidden';
                            group.style.margin = '0';
                            group.style.padding = '0';
                            
                            // Clear values and remove required
                            const inputs = group.querySelectorAll('input, select');
                            inputs.forEach(function(input) {
                                input.disabled = true;
                                if (input.type === 'radio' || input.type === 'checkbox') {
                                    input.checked = false;
                                } else {
                                    input.value = '';
                                }
                                input.removeAttribute('required');
                            });
                        }
                    }
                });
            }
           
           function toggleJenisKasusFields(jenisKasus) {
                console.log('Jenis kasus selected:', jenisKasus);
                
                // Define the mapping of jenis kasus to field names
                const fieldMapping = {
                    'Bacterial': 'bacterial',
                    'Viral': 'virus',
                    'Parasit': 'parasit',
                    'Jamur': 'jamur',
                    'Lain-lain': 'lain_lain',
                    'Lambat puncak': null // No field to show for Lambat puncak
                };
                
                // Get the field name for the selected jenis kasus
                const targetFieldName = fieldMapping[jenisKasus];
                
                if (targetFieldName === null) {
                    console.log('Lambat puncak selected - hiding all jenis kasus fields');
                    // Hide all jenis kasus related fields for Lambat puncak
                    hideAllJenisKasusFields();
                    return;
                }
                
                if (!targetFieldName) {
                    console.log('No field mapping found for:', jenisKasus);
                    return;
                }
                
                // Find all form groups in the initial questions
                const formGroups = document.querySelectorAll('#initialQuestions .form-group');
                
                // Hide all jenis kasus related fields first
                formGroups.forEach(function(group) {
                    const label = group.querySelector('label');
                    if (label) {
                        const labelText = label.textContent.toLowerCase();
                        // Check for various possible field names
                        if (labelText.includes('bacterial') || labelText.includes('virus') || 
                            labelText.includes('parasit') || labelText.includes('jamur') || 
                            labelText.includes('lain-lain') || labelText.includes('lain_lain') ||
                            labelText.includes('lambat puncak')) {
                            
                            group.classList.add('jenis-kasus-dropdown');
                            group.style.display = 'none';
                            group.style.visibility = 'hidden';
                            group.style.opacity = '0';
                            group.style.height = '0';
                            group.style.overflow = 'hidden';
                            group.style.margin = '0';
                            group.style.padding = '0';
                            
                            // Clear values and remove required
                            const inputs = group.querySelectorAll('input, select');
                            inputs.forEach(function(input) {
                                input.disabled = true;
                                if (input.type === 'radio' || input.type === 'checkbox') {
                                    input.checked = false;
                                } else {
                                    input.value = '';
                                }
                                input.removeAttribute('required');
                            });
                        }
                    }
                });
                
                // Show only the selected field
                formGroups.forEach(function(group) {
                    const label = group.querySelector('label');
                    if (label) {
                        const labelText = label.textContent.toLowerCase();
                        // Check for exact match or partial match
                        if (labelText.includes(targetFieldName.toLowerCase()) || 
                            (targetFieldName === 'virus' && labelText.includes('virus')) ||
                            (targetFieldName === 'lain_lain' && (labelText.includes('lain-lain') || labelText.includes('lain_lain')))) {
                            
                            // Remove the hiding class and show the field
                            group.classList.remove('jenis-kasus-dropdown');
                            group.style.display = 'block';
                            group.style.visibility = 'visible';
                            group.style.opacity = '1';
                            group.style.height = 'auto';
                            group.style.overflow = 'visible';
                            group.style.margin = '';
                            group.style.padding = '';
                            
                            console.log('Showing field for:', targetFieldName);
                            
                            // Enable inputs and add required attribute back if the field was originally required
                            const inputs = group.querySelectorAll('input, select');
                            inputs.forEach(function(input) {
                                input.disabled = false;
                                if (label.innerHTML.includes('<span class="required">*</span>')) {
                                    input.setAttribute('required', 'required');
                                }
                            });
                        }
                    }
                });
                
                // Also check in dynamic questions if they exist
                const dynamicFormGroups = document.querySelectorAll('#dynamicFormContent .form-group');
                
                // Hide all jenis kasus related fields in dynamic content first
                dynamicFormGroups.forEach(function(group) {
                    const label = group.querySelector('label');
                    if (label) {
                        const labelText = label.textContent.toLowerCase();
                        // Check for various possible field names
                        if (labelText.includes('bacterial') || labelText.includes('virus') || 
                            labelText.includes('parasit') || labelText.includes('jamur') || 
                            labelText.includes('lain-lain') || labelText.includes('lain_lain') ||
                            labelText.includes('lambat puncak')) {
                            
                            group.classList.add('jenis-kasus-dropdown');
                            group.style.display = 'none';
                            group.style.visibility = 'hidden';
                            group.style.opacity = '0';
                            group.style.height = '0';
                            group.style.overflow = 'hidden';
                            group.style.margin = '0';
                            group.style.padding = '0';
                            
                            // Clear values and remove required
                            const inputs = group.querySelectorAll('input, select');
                            inputs.forEach(function(input) {
                                input.disabled = true;
                                if (input.type === 'radio' || input.type === 'checkbox') {
                                    input.checked = false;
                                } else {
                                    input.value = '';
                                }
                                input.removeAttribute('required');
                            });
                        }
                    }
                });
                
                // Show only the selected field in dynamic content
                dynamicFormGroups.forEach(function(group) {
                    const label = group.querySelector('label');
                    if (label) {
                        const labelText = label.textContent.toLowerCase();
                        // Check for exact match or partial match
                        if (labelText.includes(targetFieldName.toLowerCase()) || 
                            (targetFieldName === 'virus' && labelText.includes('virus')) ||
                            (targetFieldName === 'lain_lain' && (labelText.includes('lain-lain') || labelText.includes('lain_lain')))) {
                            
                            // Remove the hiding class and show the field
                            group.classList.remove('jenis-kasus-dropdown');
                            group.style.display = 'block';
                            group.style.visibility = 'visible';
                            group.style.opacity = '1';
                            group.style.height = 'auto';
                            group.style.overflow = 'visible';
                            group.style.margin = '';
                            group.style.padding = '';
                            
                            console.log('Showing field in dynamic content for:', targetFieldName);
                            
                            // Enable inputs and add required attribute back if the field was originally required
                            const inputs = group.querySelectorAll('input, select');
                            inputs.forEach(function(input) {
                                input.disabled = false;
                                if (label.innerHTML.includes('<span class="required">*</span>')) {
                                    input.setAttribute('required', 'required');
                                }
                            });
                        }
                    }
                });
            }
         
         function addConditionalDisplayLogic() {
             // Wait a bit for the DOM to be fully rendered
             setTimeout(function() {
                 // Find all radio buttons in the dynamic content
                 const dynamicContent = document.getElementById('dynamicFormContent');
                 const allRadios = dynamicContent.querySelectorAll('input[type="radio"]');
                 
                 // Find the "Pilih tujuan visit ke kemitraan" radio buttons
                 let tujuanVisitRadios = [];
                 allRadios.forEach(function(radio) {
                     const label = radio.closest('.form-group').querySelector('label');
                     if (label && label.textContent.includes('tujuan visit ke kemitraan')) {
                         tujuanVisitRadios.push(radio);
                     }
                 });
                 
                 if (tujuanVisitRadios.length > 0) {
                     tujuanVisitRadios.forEach(function(radio) {
                         radio.addEventListener('change', function() {
                             const selectedValue = this.value;
                             console.log('Selected tujuan visit:', selectedValue);
                             
                             // Find the nama kantor field by looking for the question text
                             const formGroups = dynamicContent.querySelectorAll('.form-group');
                             let namaKantorGroup = null;
                             
                             formGroups.forEach(function(group) {
                                 const label = group.querySelector('label');
                                 if (label && label.textContent.includes('Pilih nama kantor')) {
                                     namaKantorGroup = group;
                                 }
                             });
                             
                             if (namaKantorGroup) {
                                 if (selectedValue === 'Kantor Kemitraan') {
                                     namaKantorGroup.style.display = 'none';
                                     console.log('Hiding nama kantor field');
                                     // Clear the value when hiding
                                     const inputs = namaKantorGroup.querySelectorAll('input, select');
                                     inputs.forEach(function(input) {
                                         if (input.type === 'radio' || input.type === 'checkbox') {
                                             input.checked = false;
                                         } else {
                                             input.value = '';
                                         }
                                     });
                                 } else if (selectedValue === 'Peternak Kemitraan') {
                                     namaKantorGroup.style.display = 'block';
                                     console.log('Showing nama kantor field');
                                 }
                             } else {
                                 console.log('Nama kantor field not found');
                             }
                         });
                     });
                     
                     // Trigger change event on the currently selected radio button
                     const selectedRadio = tujuanVisitRadios.find(radio => radio.checked);
                     if (selectedRadio) {
                         selectedRadio.dispatchEvent(new Event('change'));
                     }
                 }
             }, 100);
         }
         
                   function addTujuanKunjunganLogic() {
              // Wait a bit for the DOM to be fully rendered
              setTimeout(function() {
                  const dynamicContent = document.getElementById('dynamicFormContent');
                  const allRadios = dynamicContent.querySelectorAll('input[type="radio"]');
                  
                  let tujuanKunjunganRadios = [];
                  allRadios.forEach(function(radio) {
                      const label = radio.closest('.form-group').querySelector('label');
                      if (label && (label.textContent.includes('Tujuan Kunjungan') || label.textContent.includes('tujuan kunjungan'))) {
                          tujuanKunjunganRadios.push(radio);
                      }
                  });
                 
                 if (tujuanKunjunganRadios.length > 0) {
                     tujuanKunjunganRadios.forEach(function(radio) {
                         radio.addEventListener('change', function() {
                             const selectedValue = this.value;
                             console.log('Selected tujuan kunjungan:', selectedValue);
                             
                             // Find the Jenis Kasus question group
                             const formGroups = dynamicContent.querySelectorAll('.form-group');
                             let jenisKasusGroup = null;
                             
                             formGroups.forEach(function(group) {
                                 const label = group.querySelector('label');
                                 if (label && (label.textContent.includes('Jenis Kasus') || label.textContent.includes('jenis kasus'))) {
                                     jenisKasusGroup = group;
                                 }
                             });
                             
                                                           if (jenisKasusGroup) {
                                  if (selectedValue === 'Monitoring') {
                                      jenisKasusGroup.style.display = 'none';
                                      console.log('Hiding Jenis Kasus field for Monitoring');
                                      const inputs = jenisKasusGroup.querySelectorAll('input, select');
                                      inputs.forEach(function(input) {
                                          if (input.type === 'radio' || input.type === 'checkbox') {
                                              input.checked = false;
                                          } else {
                                              input.value = '';
                                          }
                                          // Remove required attribute
                                          input.removeAttribute('required');
                                      });
                                  } else if (selectedValue === 'Kasus') {
                                      jenisKasusGroup.style.display = 'block';
                                      console.log('Showing Jenis Kasus field for Kasus');
                                      // Add required attribute back if the field was originally required
                                      const inputs = jenisKasusGroup.querySelectorAll('input, select');
                                      inputs.forEach(function(input) {
                                          const label = jenisKasusGroup.querySelector('label');
                                          if (label && label.innerHTML.includes('<span class="required">*</span>')) {
                                              input.setAttribute('required', 'required');
                                          }
                                      });
                                  }
                             } else {
                                 console.log('Jenis Kasus field not found in dynamic content');
                             }
                         });
                     });
                     
                     // Trigger change event on the currently selected radio button
                     const selectedRadio = tujuanKunjunganRadios.find(radio => radio.checked);
                     if (selectedRadio) {
                         selectedRadio.dispatchEvent(new Event('change'));
                     }
                 }
             }, 100);
         }
         
         function addJenisKasusFieldsLogic() {
              // Wait a bit for the DOM to be fully rendered
              setTimeout(function() {
                  const dynamicContent = document.getElementById('dynamicFormContent');
                  const allRadios = dynamicContent.querySelectorAll('input[type="radio"]');
                  
                  let jenisKasusRadios = [];
                  allRadios.forEach(function(radio) {
                      const label = radio.closest('.form-group').querySelector('label');
                      if (label && (label.textContent.includes('Jenis Kasus') || label.textContent.includes('jenis kasus'))) {
                          jenisKasusRadios.push(radio);
                      }
                  });
                  
                  if (jenisKasusRadios.length > 0) {
                      jenisKasusRadios.forEach(function(radio) {
                          radio.addEventListener('change', function() {
                              const selectedValue = this.value;
                              console.log('Selected jenis kasus:', selectedValue);
                              
                              // Define the mapping of jenis kasus to field names
                              const fieldMapping = {
                                  'Bacterial': 'bacterial',
                                  'Viral': 'virus',
                                  'Parasit': 'parasit',
                                  'Jamur': 'jamur',
                                  'Lain-lain': 'lain_lain',
                                  'Lambat puncak': null // No field to show for Lambat puncak
                              };
                              
                              // Get the field name for the selected jenis kasus
                              const targetFieldName = fieldMapping[selectedValue];
                              
                              if (targetFieldName === null) {
                                  console.log('Lambat puncak selected - hiding all jenis kasus fields');
                                  // Hide all jenis kasus related fields for Lambat puncak
                                  hideAllJenisKasusFields();
                                  return;
                              }
                              
                              if (!targetFieldName) {
                                  console.log('No field mapping found for:', selectedValue);
                                  return;
                              }
                              
                              // Find all form groups in the dynamic content
                              const dynamicFormGroups = dynamicContent.querySelectorAll('.form-group');
                              
                              // Hide all jenis kasus related fields first
                              dynamicFormGroups.forEach(function(group) {
                                  const label = group.querySelector('label');
                                  if (label) {
                                      const labelText = label.textContent.toLowerCase();
                                      // Check for various possible field names
                                      if (labelText.includes('bacterial') || labelText.includes('virus') || 
                                          labelText.includes('parasit') || labelText.includes('jamur') || 
                                          labelText.includes('lain-lain') || labelText.includes('lain_lain') ||
                                          labelText.includes('lambat puncak')) {
                                          
                                          group.classList.add('jenis-kasus-dropdown');
                                          group.style.display = 'none';
                                          group.style.visibility = 'hidden';
                                          group.style.opacity = '0';
                                          group.style.height = '0';
                                          group.style.overflow = 'hidden';
                                          group.style.margin = '0';
                                          group.style.padding = '0';
                                          
                                          // Clear values and remove required
                                          const inputs = group.querySelectorAll('input, select');
                                          inputs.forEach(function(input) {
                                              input.disabled = true;
                                              if (input.type === 'radio' || input.type === 'checkbox') {
                                                  input.checked = false;
                                              } else {
                                                  input.value = '';
                                              }
                                              input.removeAttribute('required');
                                          });
                                      }
                                  }
                              });
                              
                              // Show only the selected field
                              dynamicFormGroups.forEach(function(group) {
                                  const label = group.querySelector('label');
                                  if (label) {
                                      const labelText = label.textContent.toLowerCase();
                                      // Check for exact match or partial match
                                      if (labelText.includes(targetFieldName.toLowerCase()) || 
                                          (targetFieldName === 'virus' && labelText.includes('virus')) ||
                                          (targetFieldName === 'lain_lain' && (labelText.includes('lain-lain') || labelText.includes('lain_lain')))) {
                                          
                                          // Remove the hiding class and show the field
                                          group.classList.remove('jenis-kasus-dropdown');
                                          group.style.display = 'block';
                                          group.style.visibility = 'visible';
                                          group.style.opacity = '1';
                                          group.style.height = 'auto';
                                          group.style.overflow = 'visible';
                                          group.style.margin = '';
                                          group.style.padding = '';
                                          
                                          console.log('Showing field for:', targetFieldName);
                                          
                                          // Enable inputs and add required attribute back if the field was originally required
                                          const inputs = group.querySelectorAll('input, select');
                                          inputs.forEach(function(input) {
                                              input.disabled = false;
                                              if (label.innerHTML.includes('<span class="required">*</span>')) {
                                                  input.setAttribute('required', 'required');
                                              }
                                          });
                                      }
                                  }
                              });
                          });
                      });
                      
                      // Trigger change event on the currently selected radio button
                      const selectedRadio = jenisKasusRadios.find(radio => radio.checked);
                      if (selectedRadio) {
                          selectedRadio.dispatchEvent(new Event('change'));
                      }
                  }
              }, 100);
          }
         
                   
         
                                       // Initialize the form on page load
            document.addEventListener('DOMContentLoaded', function() {
                console.log('DOM loaded - form initialized');
                
                // Hide all jenis kasus dropdown fields on page load first
                setTimeout(function() {
                    hideJenisKasusDropdownFields();
                    forceHideJenisKasusFields();
                    console.log('Initial hiding of jenis kasus dropdown fields completed');
                }, 100);
                
                // Initialize conditional logic for Tujuan Kunjungan in initial questions
                initializeTujuanKunjunganLogic();
                
                // Initialize conditional logic for Jenis Kasus Fields in initial questions
                initializeJenisKasusFieldsLogic();
                
                // Additional hiding with longer delay to ensure all elements are rendered
                setTimeout(function() {
                    hideJenisKasusDropdownFields();
                    forceHideJenisKasusFields();
                    console.log('Additional hiding of jenis kasus fields completed');
                }, 500);
                
                // Final hiding with even longer delay
                setTimeout(function() {
                    hideJenisKasusDropdownFields();
                    forceHideJenisKasusFields();
                    console.log('Final hiding of jenis kasus fields completed');
                }, 1000);
            });
           
           function initializeTujuanKunjunganLogic() {
               // Find all radio buttons for tujuan kunjungan in initial questions
               const initialRadios = document.querySelectorAll('#initialQuestions input[type="radio"]');
               let tujuanKunjunganRadios = [];
               
               initialRadios.forEach(function(radio) {
                   const label = radio.closest('.form-group').querySelector('label');
                   if (label && (label.textContent.includes('Tujuan Kunjungan') || label.textContent.includes('tujuan kunjungan'))) {
                       tujuanKunjunganRadios.push(radio);
                   }
               });
               
               if (tujuanKunjunganRadios.length > 0) {
                   tujuanKunjunganRadios.forEach(function(radio) {
                       radio.addEventListener('change', function() {
                           toggleJenisKasus(this.value);
                       });
                   });
                   
                   // Trigger change event on the currently selected radio button
                   const selectedRadio = tujuanKunjunganRadios.find(radio => radio.checked);
                   if (selectedRadio) {
                       selectedRadio.dispatchEvent(new Event('change'));
                   }
               }
           }
           
           function initializeJenisKasusFieldsLogic() {
               // Find all radio buttons for jenis kasus in initial questions
               const initialRadios = document.querySelectorAll('#initialQuestions input[type="radio"]');
               let jenisKasusRadios = [];
               
               initialRadios.forEach(function(radio) {
                   const label = radio.closest('.form-group').querySelector('label');
                   if (label && (label.textContent.includes('Jenis Kasus') || label.textContent.includes('jenis kasus'))) {
                       jenisKasusRadios.push(radio);
                   }
               });
               
               if (jenisKasusRadios.length > 0) {
                   jenisKasusRadios.forEach(function(radio) {
                       radio.addEventListener('change', function() {
                           toggleJenisKasusFields(this.value);
                       });
                   });
                   
                   // Trigger change event on the currently selected radio button
                   const selectedRadio = jenisKasusRadios.find(radio => radio.checked);
                   if (selectedRadio) {
                       selectedRadio.dispatchEvent(new Event('change'));
                   }
               }
           }

            function hideJenisKasusDropdownFields() {
                console.log('Executing hideJenisKasusDropdownFields...');
                
                // Find all form groups in the initial questions
                const formGroups = document.querySelectorAll('#initialQuestions .form-group');
                console.log('Found', formGroups.length, 'form groups in initial questions');
                
                // Hide only dropdown fields related to jenis kasus
                formGroups.forEach(function(group) {
                    const label = group.querySelector('label');
                    if (label) {
                        const labelText = label.textContent.toLowerCase();
                        console.log('Checking label:', labelText);
                        
                        // Check for dropdown fields only (not radio buttons)
                        if ((labelText.includes('bacterial') || labelText.includes('virus') || 
                            labelText.includes('parasit') || labelText.includes('jamur') || 
                            labelText.includes('lain-lain') || labelText.includes('lain_lain')) && 
                            !labelText.includes('jenis kasus')) {
                            
                            console.log('Hiding field:', labelText);
                            group.classList.add('jenis-kasus-dropdown');
                            group.style.display = 'none';
                            group.style.visibility = 'hidden';
                            group.style.opacity = '0';
                            group.style.height = '0';
                            group.style.overflow = 'hidden';
                            group.style.margin = '0';
                            group.style.padding = '0';
                            
                            // Clear values and remove required
                            const inputs = group.querySelectorAll('input, select');
                            inputs.forEach(function(input) {
                                input.disabled = true;
                                if (input.type === 'radio' || input.type === 'checkbox') {
                                    input.checked = false;
                                } else {
                                    input.value = '';
                                }
                                input.removeAttribute('required');
                            });
                        }
                    }
                });
                
                // Also check in dynamic questions if they exist
                const dynamicFormGroups = document.querySelectorAll('#dynamicFormContent .form-group');
                console.log('Found', dynamicFormGroups.length, 'form groups in dynamic content');
                
                // Hide only dropdown fields in dynamic content
                dynamicFormGroups.forEach(function(group) {
                    const label = group.querySelector('label');
                    if (label) {
                        const labelText = label.textContent.toLowerCase();
                        console.log('Checking dynamic label:', labelText);
                        
                        // Check for dropdown fields only (not radio buttons)
                        if ((labelText.includes('bacterial') || labelText.includes('virus') || 
                            labelText.includes('parasit') || labelText.includes('jamur') || 
                            labelText.includes('lain-lain') || labelText.includes('lain_lain')) && 
                            !labelText.includes('jenis kasus')) {
                            
                            console.log('Hiding dynamic field:', labelText);
                            group.classList.add('jenis-kasus-dropdown');
                            group.style.display = 'none';
                            group.style.visibility = 'hidden';
                            group.style.opacity = '0';
                            group.style.height = '0';
                            group.style.overflow = 'hidden';
                            group.style.margin = '0';
                            group.style.padding = '0';
                            
                            // Clear values and remove required
                            const inputs = group.querySelectorAll('input, select');
                            inputs.forEach(function(input) {
                                input.disabled = true;
                                if (input.type === 'radio' || input.type === 'checkbox') {
                                    input.checked = false;
                                } else {
                                    input.value = '';
                                }
                                input.removeAttribute('required');
                            });
                        }
                    }
                });
                
                console.log('hideJenisKasusDropdownFields completed');
            }
            
            function forceHideJenisKasusFields() {
                console.log('Executing forceHideJenisKasusFields...');
                
                // Force hide with multiple approaches
                const allFormGroups = document.querySelectorAll('.form-group');
                let hiddenCount = 0;
                
                allFormGroups.forEach(function(group) {
                    const label = group.querySelector('label');
                    if (label) {
                        const labelText = label.textContent.toLowerCase();
                        
                        // Check for jenis kasus dropdown fields
                        if ((labelText.includes('bacterial') || labelText.includes('virus') || 
                            labelText.includes('parasit') || labelText.includes('jamur') || 
                            labelText.includes('lain-lain') || labelText.includes('lain_lain')) && 
                            !labelText.includes('jenis kasus')) {
                            
                            // Multiple approaches to hide
                            group.classList.add('jenis-kasus-dropdown');
                            group.style.display = 'none';
                            group.style.visibility = 'hidden';
                            group.style.opacity = '0';
                            group.style.height = '0';
                            group.style.overflow = 'hidden';
                            group.style.margin = '0';
                            group.style.padding = '0';
                            
                            // Clear all inputs
                            const inputs = group.querySelectorAll('input, select, textarea');
                            inputs.forEach(function(input) {
                                if (input.type === 'radio' || input.type === 'checkbox') {
                                    input.checked = false;
                                } else {
                                    input.value = '';
                                }
                                input.removeAttribute('required');
                                input.disabled = true;
                            });
                            
                            hiddenCount++;
                            console.log('Force hidden field:', labelText);
                        }
                    }
                });
                
                console.log('Force hidden', hiddenCount, 'jenis kasus fields');
            }
    </script>
</body>
</html> 
