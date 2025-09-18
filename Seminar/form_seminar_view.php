<!DOCTYPE html>
<html>
<head>
    <title>Seminar</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-container{margin-left:20px}
        .page-title{margin-left:10px}
        .question-group{margin-bottom:15px}
        .custom-dropdown{position:relative;max-width:400px}
        .dropdown-toggle{background:#fff;color:#333;border:1px solid #dee2e6;cursor:pointer;text-align:left;display:flex;justify-content:space-between;align-items:center}
        .dropdown-toggle:hover,.dropdown-toggle:focus{background:#f8f9fa;border-color:#0d6efd}
        .dropdown-toggle::after{content:"▼";font-size:12px}
        .search-input{border:none;border-bottom:1px solid #dee2e6;background:#f8f9fa}
        .search-input:focus{outline:2px solid #0d6efd;background:#fff}
        .dropdown-content{display:none;position:absolute;background:#fff;min-width:100%;max-height:200px;overflow-y:auto;border:1px solid #dee2e6;border-radius:0 0 .375rem .375rem;border-top:none;z-index:1000;box-shadow:0 .125rem .25rem rgba(0,0,0,.075)}
        .dropdown-content .option-item{color:#333;padding:10px 12px;text-decoration:none;display:block;cursor:pointer;border-bottom:1px solid #eee}
        .dropdown-content .option-item:hover{background:#f8f9fa}
        .show{display:block}
        .selected-option{background:#fff;border-color:#dee2e6}
    </style>
</head>
<body>
    <div class="container-fluid">
        <h2 class="page-title mb-4">Seminar</h2>
        <form method="post" id="seminarForm" class="form-container" onsubmit="return validateForm()">
            <?php if (!empty($questions)): 
                foreach ($questions as $q): ?>
                    <div class="question-group">
                        <label class="form-label fw-bold mb-1">
                            <?= $q['question_text'] ?>
                            <?= !empty($q['required']) ? '<span class="text-danger">*</span>' : '' ?>
                        </label>

                        <?php if ($q['type'] == 'radio' && !empty($q['options'])): ?>
                            <div class="mt-1">
                                <?php foreach ($q['options'] as $opt): ?>
                                    <div class="form-check my-1">
                                        <input class="form-check-input" type="radio" name="q<?= $q['questions_id'] ?>" 
                                               value="<?= $opt['option_text'] ?>" id="r_<?= $q['questions_id'] ?>_<?= $opt['options_id'] ?? rand() ?>"
                                               <?= !empty($q['required']) ? 'required' : '' ?> 
                                               onchange="toggleOther(<?= $q['questions_id'] ?>, '<?= $opt['option_text'] ?>')">
                                        <label class="form-check-label" for="r_<?= $q['questions_id'] ?>_<?= $opt['options_id'] ?? rand() ?>">
                                            <?= $opt['option_text'] ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                                <div id="other_<?= $q['questions_id'] ?>" class="mt-2 d-none">
                                    <input type="text" name="q<?= $q['questions_id'] ?>_other" class="form-control" 
                                           placeholder="Please specify..." style="max-width:400px">
                                </div>
                            </div>

                        <?php elseif ($q['type'] == 'select' && !empty($q['options'])): ?>
                            <div class="custom-dropdown mt-1">
                                <input type="hidden" name="q<?= $q['questions_id'] ?>" id="h_<?= $q['questions_id'] ?>" 
                                       data-custom-dropdown="true" <?= !empty($q['required']) ? 'required' : '' ?>>
                                <button type="button" onclick="toggleDropdown(<?= $q['questions_id'] ?>)" 
                                        class="btn dropdown-toggle w-100" id="t_<?= $q['questions_id'] ?>">
                                    <span id="s_<?= $q['questions_id'] ?>">-- Pilih Jawaban --</span>
                                </button>
                                <div id="d_<?= $q['questions_id'] ?>" class="dropdown-content w-100">
                                    <input type="text" placeholder="Search..." id="i_<?= $q['questions_id'] ?>" 
                                           class="form-control search-input" onkeyup="filterOptions(<?= $q['questions_id'] ?>)">
                                    <?php foreach ($q['options'] as $opt): ?>
                                        <div class="option-item" onclick="selectOption(<?= $q['questions_id'] ?>, '<?= $opt['option_text'] ?>')">
                                            <?= $opt['option_text'] ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div id="other_<?= $q['questions_id'] ?>" class="mt-2 d-none">
                                <input type="text" name="q<?= $q['questions_id'] ?>_other" class="form-control" 
                                       placeholder="Please specify..." style="max-width:400px">
                            </div>

                        <?php elseif ($q['type'] == 'text'): ?>
                            <input type="text" name="q<?= $q['questions_id'] ?>" class="form-control mt-1" 
                                   placeholder="Masukkan jawaban" style="max-width:400px" 
                                   <?= !empty($q['required']) ? 'required' : '' ?>>

                        <?php elseif ($q['type'] == 'date'): ?>
                            <input type="date" name="q<?= $q['questions_id'] ?>" class="form-control mt-1" 
                                   style="max-width:400px" <?= !empty($q['required']) ? 'required' : '' ?>>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <button type="submit" class="btn btn-primary px-4 py-2 mt-4">Submit</button>
            <?php else: ?>
                <div class="alert alert-info">
                    <p class="mb-0 fst-italic">Tidak ada pertanyaan tersedia.</p>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleOther(qId, val) {
            const o = document.getElementById('other_' + qId);
            const i = o.querySelector('input');
            if (val.toLowerCase() === 'other') {
                o.classList.remove('d-none');
                i.required = true;
            } else {
                o.classList.add('d-none');
                i.value = '';
                i.required = false;
            }
        }

        function validateForm() {
            const groups = {};
            document.querySelectorAll('input[type="radio"]:checked, input[data-custom-dropdown]').forEach(el => {
                if (el.value) groups[el.name] = el.value;
            });

            for (const name in groups) {
                if (groups[name].toLowerCase() === 'other') {
                    const qId = name.replace('q', '');
                    const other = document.querySelector('input[name="' + name + '_other"]');
                    if (!other || !other.value.trim()) {
                        alert('Please specify the "Other" option for question ' + qId);
                        if (other) other.focus();
                        return false;
                    }
                }
            }
            return true;
        }

        function toggleDropdown(qId) {
            const d = document.getElementById('d_' + qId);
            d.classList.toggle('show');
            if (d.classList.contains('show')) {
                setTimeout(() => document.getElementById('i_' + qId).focus(), 100);
            }
        }

        function selectOption(qId, text) {
            document.getElementById('h_' + qId).value = text;
            document.getElementById('s_' + qId).textContent = text;
            document.getElementById('t_' + qId).classList.add('selected-option');
            document.getElementById('d_' + qId).classList.remove('show');
            document.getElementById('i_' + qId).value = '';
            document.querySelectorAll('#d_' + qId + ' .option-item').forEach(o => o.style.display = 'block');
            toggleOther(qId, text);
        }

        function filterOptions(qId) {
            const f = document.getElementById('i_' + qId).value.toUpperCase();
            document.querySelectorAll('#d_' + qId + ' .option-item').forEach(o => {
                o.style.display = (o.textContent.toUpperCase().indexOf(f) > -1) ? 'block' : 'none';
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.addEventListener('click', e => {
                if (!e.target.closest('.custom-dropdown')) {
                    document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('show'));
                }
            });
        });
    </script>
</body>
</html>
