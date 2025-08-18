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
    </style>
</head>
<body>
    <h2>Form Petelur</h2>

    <form method="post" action="">
        <?php if (!empty($questions)): ?>
            <?php foreach ($questions as $q): ?>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>
                        <?= $q['question_text'] ?>
                        <?php if (!empty($q['required'])): ?> <span style="color: red">*</span> <?php endif; ?>
                    </label>
                    <br>
                    <?php if ($q['type'] === 'select' && !empty($q['options'])): ?>
                        <select name="q<?= $q['questions_id'] ?>" class="form-control">
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
                        <input type="text" name="q<?= $q['questions_id'] ?>" <?= !empty($q['required']) ? 'required' : '' ?>><br>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <input type="submit" value="Submit">
        <?php else: ?>
            <p>Tidak ada pertanyaan.</p>
        <?php endif; ?>
    </form>

</body>
</html>
