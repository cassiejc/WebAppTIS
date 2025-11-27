<!DOCTYPE html>
<html>
<head>
    <title><?= isset($page_title) ? $page_title : 'Form Edit Data' ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-container { margin-left: 20px; }
        .page-title { margin-left: 10px; }
        .question-group { margin-bottom: 15px; }
        .dependent-field { display: none; }
        .custom-dropdown { position: relative; max-width: 400px; }
        .dropdown-toggle { 
            background: #fff; 
            color: #333; 
            border: 1px solid #dee2e6; 
            cursor: pointer; 
            text-align: left; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
        }
        .dropdown-toggle:hover, .dropdown-toggle:focus { 
            background: #f8f9fa; 
            border-color: #0d6efd; 
        }
        .dropdown-toggle::after { 
            content: "?"; 
            font-size: 12px; 
        }
        .dropdown-content { 
            display: none; 
            position: absolute; 
            background: #fff; 
            min-width: 100%; 
            max-height: 200px; 
            overflow-y: auto; 
            border: 1px solid #dee2e6; 
            border-radius: 0 0 .375rem .375rem; 
            border-top: none; 
            z-index: 1000; 
            box-shadow: 0 .125rem .25rem rgba(0,0,0,.075);
        }
        .dropdown-content .dropdown-option { 
            color: #333; 
            padding: 10px 12px; 
            text-decoration: none; 
            display: block; 
            cursor: pointer; 
            border-bottom: 1px solid #eee;
        }
        .dropdown-content .dropdown-option:hover { 
            background: #f8f9fa; 
        }
        .show { display: block; }
        .selected-item { background: #fff; border-color: #dee2e6; }
        .form-label { font-weight: 600; }
        .btn-secondary { margin-right: 10px; }
        .kontributor-list {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            padding: 10px;
            border-radius: .375rem;
            max-width: 500px;
        }
        .btn-sm {
            font-size: 14px;
            padding: 6px 12px;
        }
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        .modal-header.bg-success {
            background-color: #28a745 !important;
        }
        .btn-close-white {
            filter: brightness(0) invert(1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <h2 class="page-title mb-4"><?= isset($page_title) ? $page_title : 'Form Edit Data' ?></h2>
    <?php
    if ($this->session->flashdata('error')) {
       echo '<div class="alert alert-danger">' . $this->session->flashdata('error') . '</div>';
    }
    if ($this->session->flashdata('success')) {
      echo '<div class="alert alert-success">' . $this->session->flashdata('success') . '</div>';
    }
    ?>
        <?php
            $action_url = isset($form_action) ? $form_action : ''; 
            $attributes = ['class' => 'form-container', 'id' => 'mainForm'];
            echo form_open($action_url, $attributes);
        ?>

        <?php if (isset($edit_id) && !empty($edit_id)): ?>
            <input type="hidden" name="edit_id" value="<?= htmlspecialchars($edit_id) ?>">
        <?php endif; ?>    

        <?php if (isset($kategori_selected) && $kategori_selected == 'Kontributor Harga'): ?>
                        
            <?php if (isset($semua_jenis_harga) && !empty($semua_jenis_harga)): ?>
                <div class="question-group">
                    <label class="form-label fw-bold mb-2">Pilih Harga yang Akan Diikuti Kontributor:</label>
                    <div class="kontributor-list">
                        <?php 
                        $kontribusi_terpilih = isset($existing_data['kontribusi_terpilih']) ? $existing_data['kontribusi_terpilih'] : [];

                        foreach ($semua_jenis_harga as $key => $label) : 
                        
                            $is_checked = in_array($key, $kontribusi_terpilih);
                        ?>
                            <div class="form-check my-1">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       name="kontribusi_harga[]"  
                                       value="<?= htmlspecialchars($key); ?>" 
                                       id="check_<?= htmlspecialchars($key); ?>"
                                       <?= $is_checked ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="check_<?= htmlspecialchars($key); ?>">
                                    <?= htmlspecialchars($label); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            
                <div class="mt-4">
                  
                    <a href="<?= site_url('Admin_Controller/list_data/kontributorharga') ?>" class="btn btn-secondary px-4 py-2 mb-2">Batal</a>
                    <button type="submit" name="submit_form" value="1" class="btn btn-primary px-4 py-2 mb-2">Update</button>
                </div>
                
            <?php else: ?>
                <div class="alert alert-warning">
                    <p class="mb-0 fst-italic">Gagal memuat daftar jenis harga untuk kontributor.</p>
                </div>
                 <div class="mt-4">
                    <a href="<?= site_url('Admin_Controller/list_data/kontributorharga') ?>" class="btn btn-secondary px-4 py-2">Kembali</a>
                </div>
            <?php endif; ?>

        <?php else: ?>
        

            <?php if (!empty($questions_kategori)): ?>
                
                <?php foreach ($questions_kategori as $q): ?>
                    <?php
                    $current_value = '';
                    if (isset($existing_data[$q['field_name']])) {
                        $current_value = $existing_data[$q['field_name']];
                    }
                    
                    $jenis_peternak_value = '';
                    $selected_dari_value = '';
                    if ($q['field_name'] == 'jenis_peternak' && !empty($current_value)) {
                        if (strpos($current_value, ':') !== false) {
                            $parts = explode(':', $current_value, 2);
                            $jenis_peternak_value = trim($parts[0]);
                            $selected_dari_value = trim($parts[1]);
                        } else {
                            $jenis_peternak_value = $current_value;
                        }
                        $current_value = $jenis_peternak_value;
                    }
                    
                    if (in_array($q['field_name'], ['agen_dari', 'sub_agen_dari', 'kemitraan_dari'])) {
                        $current_value = $selected_dari_value;
                    }
                    ?>
                    
                    <div class="question-group <?= (in_array($q['field_name'], ['agen_dari', 'sub_agen_dari', 'kemitraan_dari'])) ? 'dependent-field' : '' ?>" 
                         id="field-<?= $q['field_name'] ?>"
                         <?= ($q['field_name'] == 'jenis_peternak') ? 'data-jenis-peternak="true"' : '' ?>>
                        <label class="form-label fw-bold mb-1">
                            <?= $q['question_text'] ?>
                            <?php if (!empty($q['required'])): ?>
                                <span class="text-danger">*</span>
                            <?php endif; ?>
                        </label>
                        
                        <?php if ($q['type'] == 'number' || $q['field_name'] == 'kapasitas_peternak' || $q['field_name'] == 'jumlah_kandang_peternak' || $q['field_name'] == 'kapasitas_farm'): ?>
                            <input type="text"
                                   inputmode="numeric" 
                                   class="form-control mt-1 numeric-input"
                                   style="max-width: 400px"
                                   name="q<?= $q['questions_id'] ?>" 
                                   value="<?= htmlspecialchars($current_value) ?>"
                                   placeholder="Masukkan angka"
                                   <?= !empty($q['required']) ? 'required' : '' ?>>

                        <?php elseif ($q['type'] == 'text_readonly'): ?>
                            <input type="text" 
                                   name="q<?= $q['questions_id'] ?>" 
                                   class="form-control mt-1"
                                   style="max-width: 400px"
                                   value="<?= htmlspecialchars($current_value) ?>"
                                   readonly>
                            <div class="form-text">Data ini tidak dapat diubah.</div>

                        <?php elseif ($q['type'] == 'text'): ?>
                            <input type="text" 
                                   name="q<?= $q['questions_id'] ?>" 
                                   class="form-control mt-1"
                                   style="max-width: 400px"
                                   value="<?= htmlspecialchars($current_value) ?>"
                                   placeholder="Masukkan jawaban"
                                   <?= !empty($q['required']) ? 'required' : '' ?>>
                                   
                        <?php elseif ($q['type'] == 'date'): ?>
                            <input type="date" 
                                   name="q<?= $q['questions_id'] ?>" 
                                   class="form-control mt-1"
                                   style="max-width: 400px"
                                   value="<?= htmlspecialchars($current_value) ?>"
                                   <?= !empty($q['required']) ? 'required' : '' ?>>

                        <?php elseif ($q['type'] == 'radio' && !empty($q['options'])): ?>
                            <div class="mt-1">
                                <?php foreach ($q['options'] as $opt): ?>
                                    <div class="form-check my-1">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="q<?= $q['questions_id'] ?>" 
                                               value="<?= $opt['option_text'] ?>"
                                               id="r_<?= $q['questions_id'] ?>_<?= $opt['options_id'] ?? rand() ?>"
                                               <?= ($current_value == $opt['option_text']) ? 'checked' : '' ?>
                                               <?= !empty($q['required']) ? 'required' : '' ?>>
                                        <label class="form-check-label" 
                                               for="r_<?= $q['questions_id'] ?>_<?= $opt['options_id'] ?? rand() ?>">
                                            <?= $opt['option_text'] ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>


                    <?php elseif ($q['type'] == 'select'): ?>
                        <?php 
                            $onchange_attr = ($q['field_name'] == 'jenis_peternak') ? 'onchange="toggleDependentFields(this.value)"' : '';
                            
                            $is_area_field = ($q['field_name'] == 'master_area_id');
                            $is_sub_area_field = ($q['field_name'] == 'master_sub_area_id');
                        ?>
                        
                        <div class="d-flex align-items-center gap-2" style="max-width: 500px;">
                            <select name="q<?= $q['questions_id'] ?>" 
                                    id="select_<?= $q['field_name'] ?>"
                                    class="form-select mt-1 flex-grow-1"
                                    <?= !empty($q['required']) ? 'required' : '' ?>
                                    <?= $onchange_attr ?>>
                                <option value="">-- Pilih Jawaban --</option>
                                <?php if (!empty($q['options'])): ?>
                                    <?php foreach ($q['options'] as $opt): ?>
                                        <?php
                                            $option_value = isset($opt['option_value']) ? $opt['option_value'] : $opt['option_text'];
                                            $option_text = $opt['option_text'];
                                        ?>
                                        <option value="<?= htmlspecialchars($option_value) ?>" <?= ($current_value == $option_value) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($option_text) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>Tidak ada opsi tersedia</option>
                                <?php endif; ?>
                            </select>
                            
                            <?php if ($is_area_field): ?>
                                <button type="button" 
                                        class="btn btn-success btn-sm mt-1" 
                                        style="min-width: 40px; height: 38px;"
                                        onclick="openAddAreaModal()"
                                        title="Tambah Area Baru">
                                    <i class="fas fa-plus"></i>
                                </button>
                            <?php endif; ?>
                            
                            <?php if ($is_sub_area_field): ?>
                                <button type="button" 
                                        class="btn btn-success btn-sm mt-1" 
                                        style="min-width: 40px; height: 38px;"
                                        onclick="openAddSubAreaModal()"
                                        title="Tambah Sub-Area Baru">
                                    <i class="fas fa-plus"></i>
                                </button>
                            <?php endif; ?>
                        </div>

                        <?php elseif ($q['type'] == 'checkbox'): ?>
                            
                            <?php if (!empty($q['options'])): ?>
                                <?php 
                                $selected_checkboxes = [];
                                if (!empty($current_value)) {
                                    $selected_checkboxes = is_array($current_value) ? $current_value : explode(',', $current_value);
                                }
                                ?>
                                <div class="mt-1">
                                    <?php foreach ($q['options'] as $opt): ?>
                                        <div class="form-check my-1">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   name="q<?= $q['questions_id'] ?>[]" 
                                                   value="<?= $opt['option_text'] ?>"
                                                   id="c_<?= $q['questions_id'] ?>_<?= $opt['options_id'] ?? rand() ?>"
                                                   <?= in_array($opt['option_text'], $selected_checkboxes) ? 'checked' : '' ?>>
                                            <label class="form-check-label" 
                                                   for="c_<?= $q['questions_id'] ?>_<?= $opt['options_id'] ?? rand() ?>">
                                                <?= $opt['option_text'] ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                            <?php else: ?>
                                <div class="form-check mt-1">
                                    <input 
                                        class="form-check-input" 
                                        type="checkbox" 
                                        name="q<?= $q['questions_id'] ?>" 
                                        id="q<?= $q['questions_id'] ?>" 
                                        value="1"
                                        <?= ($current_value == '1') ? 'checked' : '' ?>
                                    >
                                    <label class="form-check-label" for="q<?= $q['questions_id'] ?>">
                                        <?= (isset($q['question_text']) && $q['question_text'] == 'Status Kontributor') ? 'Ya, jadikan sebagai kontributor' : 'Ya' ?>
                                    </label>
                                </div>
                            <?php endif; ?>

                        <?php elseif ($q['type'] == 'textarea'): ?>
                            <textarea name="q<?= $q['questions_id'] ?>"
                                      class="form-control mt-1"
                                      rows="4" 
                                      style="max-width: 400px"
                                      placeholder="Masukkan jawaban"
                                      <?= !empty($q['required']) ? 'required' : '' ?>><?= htmlspecialchars($current_value) ?></textarea>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                
                <div class="mt-4">
                    <a href="<?= site_url('Dashboard_new/index') ?>" class="btn btn-secondary px-4 py-2 mb-2">Batal</a>
                    <button type="submit" name="submit_form" value="1" class="btn btn-primary px-4 py-2 mb-2">Update</button>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <p class="mb-0 fst-italic">Tidak ada pertanyaan yang tersedia untuk kategori ini.</p>
                </div>
                <div class="mt-4">
                    <a href="<?= site_url('Dashboard_new/index') ?>" class="btn btn-secondary px-4 py-2">Kembali</a>
                </div>
            <?php endif; ?>

        <?php endif; ?>
        

        <?php echo form_close(); ?>
    </div>

    <div class="modal fade" id="modalAddArea" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-map-marked-alt me-2"></i>Tambah Area Baru
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="input_nama_area" class="form-label fw-bold">Nama Area <span class="text-danger">*</span></label>
                        <input type="text" 
                            class="form-control" 
                            id="input_nama_area" 
                            placeholder="Masukkan nama area baru"
                            autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" onclick="submitAddArea()">
                        <i class="fas fa-save me-2"></i>Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalAddSubArea" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-map-marker-alt me-2"></i>Tambah Sub-Area Baru
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>Sub-Area akan ditambahkan ke Area yang sedang dipilih.</small>
                    </div>
                    <div class="mb-3">
                        <label for="input_nama_sub_area" class="form-label fw-bold">Nama Sub-Area <span class="text-danger">*</span></label>
                        <input type="text" 
                            class="form-control" 
                            id="input_nama_sub_area" 
                            placeholder="Masukkan nama sub-area baru"
                            autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" onclick="submitAddSubArea()">
                        <i class="fas fa-save me-2"></i>Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
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
                    <?php if (!empty($questions_kategori)): ?>
                    const qData = <?= json_encode($questions_kategori) ?>.find(q => q.field_name === 'agen_dari');
                    if (qData && qData.required) {
                        const inputs = agenField.querySelectorAll('input, select, textarea');
                        inputs.forEach(input => {
                            input.setAttribute('required', 'required');
                        });
                    }
                    <?php endif; ?>
                }
            } else if (selectedValue === 'Sub Agen') {
                const subAgenField = document.getElementById('field-sub_agen_dari');
                if (subAgenField) {
                    subAgenField.style.display = 'block';
                    <?php if (!empty($questions_kategori)): ?>
                    const qData = <?= json_encode($questions_kategori) ?>.find(q => q.field_name === 'sub_agen_dari');
                    if (qData && qData.required) {
                        const inputs = subAgenField.querySelectorAll('input, select, textarea');
                        inputs.forEach(input => {
                            input.setAttribute('required', 'required');
                        });
                    }
                    <?php endif; ?>
                }
            } else if (selectedValue === 'Kemitraan') {
                const kemitraanField = document.getElementById('field-kemitraan_dari');
                if (kemitraanField) {
                    kemitraanField.style.display = 'block';
                    <?php if (!empty($questions_kategori)): ?>
                    const qData = <?= json_encode($questions_kategori) ?>.find(q => q.field_name === 'kemitraan_dari');
                    if (qData && qData.required) {
                        const inputs = kemitraanField.querySelectorAll('input, select, textarea');
                        inputs.forEach(input => {
                            input.setAttribute('required', 'required');
                        });
                    }
                    <?php endif; ?>
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const jenisPeternakGroup = document.querySelector('[data-jenis-peternak="true"]');
            if (jenisPeternakGroup) {
                const jenisPeternakSelect = jenisPeternakGroup.querySelector('select');
                if (jenisPeternakSelect) {
                    toggleDependentFields(jenisPeternakSelect.value);
                }
            }

            function formatNumber(e) {
                let input = e.target;
                let value = input.value.replace(/\D/g, ''); 
                
                let cursorPosition = input.selectionStart;
                
                if (value.trim() === '') {
                    input.value = '';
                    return;
                }
                
                const lengthBeforeFormatting = input.value.length;
                let formattedValue = new Intl.NumberFormat('en-US').format(value);
                input.value = formattedValue;
                const lengthAfterFormatting = input.value.length;

                cursorPosition += (lengthAfterFormatting - lengthBeforeFormatting);
                if(cursorPosition < 0) cursorPosition = 0; 
                input.setSelectionRange(cursorPosition, cursorPosition);
            }

            document.querySelectorAll('.numeric-input').forEach(input => {
                input.addEventListener('input', formatNumber);
                if (input.value && input.value.trim() !== '') {
                    let initialValue = input.value.replace(/,/g, '');
                    if (initialValue && !isNaN(initialValue)) {
                        input.value = new Intl.NumberFormat('en-US').format(initialValue);
                    }
                }
            });

            document.getElementById('mainForm').addEventListener('submit', function(e) {
                document.querySelectorAll('.numeric-input').forEach(input => {
                    input.value = input.value.replace(/,/g, '');
                });
            });
            
            <?php if (empty($questions_kategori)): ?>
            if (typeof toggleDependentFields === 'function') {
                const originalToggle = toggleDependentFields;
                toggleDependentFields = function(selectedValue) {
                    console.log("Toggle fields (no questions data):", selectedValue);
                }
            }
            <?php endif; ?>
            
        });

    let csrfTokenName = '<?= $this->security->get_csrf_token_name(); ?>';
    let csrfHash = '<?= $this->security->get_csrf_hash(); ?>';

    function openAddAreaModal() {
        document.getElementById('input_nama_area').value = '';
        let modal = new bootstrap.Modal(document.getElementById('modalAddArea'));
        modal.show();
    }

    function submitAddArea() {
        const namaArea = document.getElementById('input_nama_area').value.trim();
        
        if (namaArea === '') {
            alert('Nama area tidak boleh kosong!');
            return;
        }
        
        const btnSubmit = event.target;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
        
        const formData = new FormData();
        formData.append('nama_area', namaArea);
        formData.append(csrfTokenName, csrfHash);
        
        fetch('<?= site_url("Admin_Controller/ajax_add_area") ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_hash) {
                csrfHash = data.csrf_hash;
            }
            
            if (data.status === 'success') {
                const selectArea = document.getElementById('select_master_area_id');
                if (selectArea) {
                    const newOption = new Option(data.data.text, data.data.id, true, true);
                    selectArea.add(newOption);
                }
                
                bootstrap.Modal.getInstance(document.getElementById('modalAddArea')).hide();
                
            }
        })
        .finally(() => {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fas fa-save me-2"></i>Simpan';
        });
    }

    function openAddSubAreaModal() {
        const selectArea = document.getElementById('select_master_area_id');
        
        if (!selectArea || selectArea.value === '') {
            alert('Pilih Area terlebih dahulu sebelum menambahkan Sub-Area!');
            return;
        }
        
        document.getElementById('input_nama_sub_area').value = '';
        let modal = new bootstrap.Modal(document.getElementById('modalAddSubArea'));
        modal.show();
    }

    function submitAddSubArea() {
        const namaSubArea = document.getElementById('input_nama_sub_area').value.trim();
        const selectArea = document.getElementById('select_master_area_id');
        const masterAreaId = selectArea ? selectArea.value : '';
        
        if (namaSubArea === '') {
            alert('Nama sub-area tidak boleh kosong!');
            return;
        }
        
        if (masterAreaId === '') {
            alert('Pilih Area terlebih dahulu!');
            return;
        }
        
        const btnSubmit = event.target;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
        
        const formData = new FormData();
        formData.append('nama_sub_area', namaSubArea);
        formData.append('master_area_id', masterAreaId);
        formData.append(csrfTokenName, csrfHash);
        
        fetch('<?= site_url("Admin_Controller/ajax_add_sub_area") ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_hash) {
                csrfHash = data.csrf_hash;
            }
            
            if (data.status === 'success') {
                const selectSubArea = document.getElementById('select_master_sub_area_id');
                if (selectSubArea) {
                    const newOption = new Option(data.data.text, data.data.id, true, true);
                    selectSubArea.add(newOption);
                }
                
                bootstrap.Modal.getInstance(document.getElementById('modalAddSubArea')).hide();
                
            }
        })
        .finally(() => {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fas fa-save me-2"></i>Simpan';
        });
    }
    </script>
</body>
</html>
