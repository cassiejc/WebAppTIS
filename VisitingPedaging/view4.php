<!DOCTYPE html>
<html>
<head>
    <title>Pedaging</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-container{margin-left:20px}.page-title{margin-left:10px}
        .custom-dropdown{position:relative}.dropdown-toggle{background:#fff;color:#333;border:1px solid #dee2e6;cursor:pointer;text-align:left;display:flex;justify-content:space-between;align-items:center}
        .dropdown-toggle:hover,.dropdown-toggle:focus{background:#f8f9fa;border-color:#0d6efd}.dropdown-toggle:disabled{background:#e9ecef;color:#6c757d;cursor:not-allowed}
        .dropdown-toggle::after{content:"▼";font-size:12px}.farm-search-input{border:none;border-bottom:1px solid #dee2e6;background:#f8f9fa}
        .farm-search-input:focus{outline:2px solid #0d6efd;background:#fff}
        .dropdown-content{display:none;position:absolute;background:#fff;min-width:100%;max-height:200px;overflow-y:auto;border:1px solid #dee2e6;border-radius:0 0 .375rem .375rem;border-top:none;z-index:1000;box-shadow:0 .125rem .25rem rgba(0,0,0,.075)}
        .dropdown-content .farm-option{color:#333;padding:10px 12px;text-decoration:none;display:block;cursor:pointer;border-bottom:1px solid #eee}
        .dropdown-content .farm-option:hover{background:#f8f9fa}.show{display:block}.selected-farm{background:#fefefeff;border-color:#dee2e6}
        .integer-input,.currency-input,.varchar-input,.letters-only-input{position:relative;max-width:400px}
        .currency-prefix{position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#666;pointer-events:none}
        .currency-input input{padding-left:30px}.loading{display:none;color:#666;font-style:italic}
        
        /* Hide scrollbar for textarea - matching petelur form */
        textarea::-webkit-scrollbar {
            display: none;
        }
        
        /* ADDED: CSS rule to hide other questions by default */
        .other-question { display: none; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <h2 class="page-title mb-4">Pedaging - <?=$current_sub_area['nama_sub_area']?></h2>
        <form method="post" id="pulletForm" class="form-container">
            <input type="hidden" id="selected_tipe_ternak" name="tipe_ternak">
            <div id="pulletQuestions">
                <?php if(!empty($questions)):
                    $jenis_ternak_q=null;$other_questions=[];
                    foreach($questions as $q){
                        if(trim(strtolower($q['question_text']))==='jenis ternak pedaging')$jenis_ternak_q=$q;
                        else $other_questions[]=$q;
                    }
                    function renderQ($q,$isJT=false){
                        $req=!empty($q['required'])?'required':'';
                        $ast=!empty($q['required'])?'<span class="text-danger">*</span>':'';
                        $inp='q'.$q['questions_id'];
                        
                        $q_class = 'mb-4 question-group' . (!$isJT ? ' other-question' : '');
                        
                        $h='<div class="'.$q_class.'" data-field="'.$q['field_name'].'"><label class="form-label fw-bold">'.$q['question_text'].' '.$ast.'</label>';
                        
                        if($q['type']=='select'&&!empty($q['options'])){
                            if($q['field_name']=='nama_farm'){
                                $h.='<div class="custom-dropdown" style="max-width:400px">
                                <input type="hidden" name="'.$inp.'" id="namaFarmHidden_'.$q['questions_id'].'" data-field="'.$q['field_name'].'" '.$req.'>
                                <button type="button" onclick="toggleFarm(\''.$q['questions_id'].'\')" class="btn dropdown-toggle w-100 disabled" id="namaFarmToggle_'.$q['questions_id'].'" disabled>
                                <span id="selectedFarmText_'.$q['questions_id'].'">-- Pilih Nama Farm --</span></button>
                                <div id="namaFarmDropdown_'.$q['questions_id'].'" class="dropdown-content w-100">
                                <input type="text" placeholder="Cari..." id="farmSearchInput_'.$q['questions_id'].'" class="form-control farm-search-input" onkeyup="filterFarm(\''.$q['questions_id'].'\')">';
                                foreach($q['options'] as $o)$h.='<div class="farm-option option-item" data-value="'.$o['option_text'].'" data-tipe="'.($o['tipe_ternak']??'').'" onclick="selectFarm(\''.$q['questions_id'].'\',\''.$o['option_text'].'\')">'.$o['option_text'].'</div>';
                                $h.='</div></div>';
                            }else{
                                $oc=$isJT?'onchange="changeTipe(this.value)"':'';
                                $dis=$q['field_name']=='nama_peternak'?'disabled':'';
                                $h.='<select name="'.$inp.'" data-field="'.$q['field_name'].'" class="form-select" style="max-width:400px" '.$oc.' '.$dis.' '.$req.'>
                                <option value="">-- Pilih --</option>';
                                foreach($q['options'] as $o)$h.='<option value="'.$o['option_text'].'" data-tipe="'.($o['tipe_ternak']??'').'" class="option-item">'.$o['option_text'].'</option>';
                                $h.='</select>';
                            }
                        }elseif($q['type']=='radio'&&!empty($q['options'])){
                            $h.='<div class="mt-2">';
                            foreach($q['options'] as $o){
                                $oid='radio_'.$q['questions_id'].'_'.($o['options_id']??rand());
                                $h.='<div class="form-check option-item" data-tipe="'.($o['tipe_ternak']??'').'">
                                <input class="form-check-input" type="radio" name="'.$inp.'" value="'.$o['option_text'].'" data-field="'.$q['field_name'].'" id="'.$oid.'" '.$req.'>
                                <label class="form-check-label" for="'.$oid.'">'.$o['option_text'].'</label></div>';
                            }$h.='</div>';
                        }elseif($q['type']=='textarea'){
                            // Fixed size textarea with auto-resize when text is long
                            $h.='<textarea name="'.$inp.'" class="form-control auto-resize-textarea" data-field="'.$q['field_name'].'" style="max-width:400px; resize: none; overflow: hidden; scrollbar-width: none; -ms-overflow-style: none;" rows="3" placeholder="Masukkan catatan..." oninput="autoResize(this)" '.$req.'></textarea>';
                        
                        // START MODIFIED BLOCK
                        }elseif($q['type']=='text'||$q['type']=='number'){
                            $cls='form-control';
                            $ph='Masukkan jawaban'; // Default placeholder for text
                            $ext=''; $w=''; $we='';
                            
                            if(isset($q['input_type'])){
                                switch($q['input_type']){
                                    case 'integer':
                                        $w='<div class="integer-input">';$we='</div>';$cls.=' integer-field';
                                        $ph='Masukkan angka bulat'; // Placeholder for integer
                                        $ext='onkeypress="return isInt(event)" oninput="formatInt(this)"';
                                        break;
                                    case 'currency':
                                        $w='<div class="currency-input"><span class="currency-prefix">Rp</span>';$we='</div>';
                                        $ph=' 0'; // Specific placeholder for currency
                                        $ext='oninput="formatCur(this)" onkeypress="return isNum(event)"';
                                        break;
                                    case 'letters_only':
                                        $w='<div class="letters-only-input">';$we='</div>';
                                        // The default placeholder "Masukkan jawaban" is already set and is appropriate here.
                                        $ext='onkeypress="return isLet(event)" oninput="filterLet(this)"';
                                        break;
                                }
                            }
                            
                            // Add this condition before the other field_name checks
                            if ($q['field_name'] === 'pedaging_harga_panen') {
                                $w = '<div class="currency-input"><span class="currency-prefix">Rp</span>';
                                $we = '</div>';
                                $ph = ' 0';
                                $ext = 'oninput="formatCur(this)" onkeypress="return isNum(event)"';
                            } 
                            elseif(in_array($q['field_name'],['efektif_terisi_pedaging'])){
                                $ext='oninput="formatNum(this)" onkeypress="return(event.charCode>=48&&event.charCode<=57)"';
                                $ph = 'Masukkan angka bulat'; // Placeholder for this specific integer field
                            } 
                            elseif($q['field_name']=='umur_pedaging'){
                                $ext='type="number" step="1" min="0"';
                                $ph = 'Masukkan angka bulat'; // Placeholder for this specific integer field
                            }
                            elseif(in_array($q['field_name'],['deplesi_pedaging','intake_pedaging','pencapaian_berat_pedaging','keseragaman_pedaging','fcr_pedaging'])){
                                $ext='type="number" step="0.01" min="0"';
                                $ph = 'Masukkan angka'; // Placeholder for decimal fields
                            }
                            
                            $h.=$w.'<input '.($ext?:'type="text"').' name="'.$inp.'" data-field="'.$q['field_name'].'" class="'.$cls.'" placeholder="'.$ph.'" style="max-width:400px" '.$req.'>'.$we;
                        // END MODIFIED BLOCK

                        }elseif($q['type']=='date'){
                            $h.='<input type="date" name="'.$inp.'" data-field="'.$q['field_name'].'" class="form-control" style="max-width:400px" '.$req.'>';
                        }elseif($q['type']=='checkbox'&&!empty($q['options'])){
                            $h.='<div class="mt-2">';
                            foreach($q['options'] as $o){
                                $oid='cb_'.$q['questions_id'].'_'.($o['options_id']??rand());
                                $h.='<div class="form-check option-item" data-tipe="'.($o['tipe_ternak']??'').'">
                                <input class="form-check-input" type="checkbox" name="'.$inp.'[]" value="'.$o['option_text'].'" id="'.$oid.'">
                                <label class="form-check-label" for="'.$oid.'">'.$o['option_text'].'</label></div>';
                            }$h.='</div>';
                        }
                        return $h.'</div>';
                    }
                    if($jenis_ternak_q)echo renderQ($jenis_ternak_q,true);
                    foreach($other_questions as $q)echo renderQ($q);
                else:?>
                    <div class="alert alert-info"><p class="mb-0 fst-italic">Tidak ada pertanyaan.</p></div>
                <?php endif?>
            </div>
            <div class="loading" id="loading">Loading...</div>
            <button type="submit" class="btn btn-primary px-4 py-2 mt-4" id="submitBtn">Submit</button>
        </form>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>

        
        // Auto-resize function for textarea - expands only when text is long
        const autoResize = (textarea) => {
            textarea.style.height = 'auto';
            const minHeight = 80; // Minimum height for 3 rows (~80px)
            const newHeight = Math.max(minHeight, textarea.scrollHeight);
            textarea.style.height = newHeight + 'px';
        };
        
        const initResize = () => {
            document.querySelectorAll('.auto-resize-textarea').forEach(textarea => {
                // Set initial height to 3 rows
                textarea.style.height = '80px';
                // Add event listeners
                ['input', 'paste', 'focus'].forEach(event => 
                    textarea.addEventListener(event, () => setTimeout(() => autoResize(textarea), 10))
                );
            });
        };
        const isLet=e=>{const c=e.which||e.keyCode;return[46,8,9,27,13,32].includes(c)||(c>=37&&c<=40)||((c>=65&&c<=90)||(c>=97&&c<=122))||(e.preventDefault(),false)};
        const filterLet=i=>i.value=i.value.replace(/[^a-zA-Z\s]/g,'').replace(/\s+/g,' ').trim();
        const isInt=e=>{const c=e.which||e.keyCode;return[46,8,9,27,13].includes(c)||(c>=37&&c<=40)||((c>=48&&c<=57)||(e.preventDefault(),false))};
        const formatInt=i=>{const v=i.value.replace(/[^\d]/g,'');i.value=v===''?'':parseInt(v,10).toLocaleString('en-US')};
        const formatCur=i=>{const v=i.value.replace(/[^\d]/g,'');i.value=v===''?'':parseInt(v).toLocaleString('en-US')};
        const isNum=e=>{const c=e.which||e.keyCode;return[46,8,9,27,13].includes(c)||(c>=48&&c<=57)};
        const formatNum=i=>{const v=i.value.replace(/\D/g,'');i.value=v!==''?parseInt(v).toLocaleString('en-US'):''};
        
        const toggleFarm=id=>{const d=document.getElementById(`namaFarmDropdown_${id}`),b=document.getElementById(`namaFarmToggle_${id}`);if(b.disabled)return;document.querySelectorAll('[id^="namaFarmDropdown_"]').forEach(dd=>{if(dd.id!==`namaFarmDropdown_${id}`)dd.classList.remove('show')});d.classList.toggle('show');if(d.classList.contains('show'))setTimeout(()=>document.getElementById(`farmSearchInput_${id}`).focus(),100)};
        const selectFarm=(id,name)=>{document.getElementById(`namaFarmHidden_${id}`).value=name;document.getElementById(`selectedFarmText_${id}`).textContent=name;document.getElementById(`namaFarmToggle_${id}`).classList.add('selected-farm');closeFarm(id);document.getElementById(`farmSearchInput_${id}`).value='';document.querySelectorAll(`#namaFarmDropdown_${id} .farm-option`).forEach(o=>{if(!o.hasAttribute('data-hidden-by-tipe'))o.style.display='block'})};
        const filterFarm=id=>{const f=document.getElementById(`farmSearchInput_${id}`).value.toUpperCase();document.querySelectorAll(`#namaFarmDropdown_${id} .farm-option`).forEach(o=>{const t=o.textContent||o.innerText;o.style.display=(t.toUpperCase().indexOf(f)>-1&&!o.hasAttribute('data-hidden-by-tipe'))?'block':'none'})};
        const closeFarm=id=>id?document.getElementById(`namaFarmDropdown_${id}`).classList.remove('show'):document.querySelectorAll('[id^="namaFarmDropdown_"]').forEach(d=>d.classList.remove('show'));
        
        const toggle=(en,tipe)=>{document.querySelectorAll('select[data-field="nama_peternak"]').forEach(s=>{s.disabled=!en;if(!en)s.value=''});document.querySelectorAll('[id^="namaFarmToggle_"]').forEach(b=>{b.disabled=!en;b.classList.toggle('disabled',!en);if(!en){const id=b.id.split('_')[1];const st=document.getElementById(`selectedFarmText_${id}`),hi=document.getElementById(`namaFarmHidden_${id}`);if(st)st.textContent='-- Pilih Nama Farm --';if(hi)hi.value='';b.classList.remove('selected-farm')}});document.querySelectorAll('[data-field*="strain"],[data-field*="pakan"]').forEach(q=>q.querySelectorAll('input,select,textarea').forEach(i=>{i.disabled=!en;if(!en){if(['radio','checkbox'].includes(i.type))i.checked=false;else i.value=''}}));if(en&&tipe)filterByTipe(tipe)};
        
        const filterByTipe=tipe=>{if(!tipe){document.querySelectorAll('.option-item').forEach(o=>{const qg=o.closest('.question-group');if(qg){const l=qg.querySelector('label');if(!l||!l.textContent.toLowerCase().includes('jenis ternak pedaging'))o.style.display='none'}});return}document.querySelectorAll('.option-item:not(.farm-option)').forEach(o=>{const ot=o.getAttribute('data-tipe')||o.dataset.tipe;o.style.display=(!ot||ot===tipe)?'block':'none'});document.querySelectorAll('.farm-option').forEach(o=>{const ot=o.getAttribute('data-tipe')||o.dataset.tipe;if(!ot||ot===tipe){o.style.display='block';o.removeAttribute('data-hidden-by-tipe')}else{o.style.display='none';o.setAttribute('data-hidden-by-tipe','true')}});document.querySelectorAll('select:not([data-field*="jenis_ternak"])').forEach(s=>{s.selectedIndex=0;s.querySelectorAll('option.option-item').forEach(o=>{const ot=o.getAttribute('data-tipe')||o.dataset.tipe;const sh=!ot||ot===tipe;o.style.display=sh?'block':'none';o.disabled=!sh})})};
        
        const changeTipe=tipe=>{
            document.getElementById('selected_tipe_ternak').value=tipe;
            const otherQuestions = document.querySelectorAll('.other-question');

            if(!tipe){
                toggle(false);
                otherQuestions.forEach(q => q.style.display = 'none');
                return;
            }

            otherQuestions.forEach(q => q.style.display = 'block');

            document.querySelectorAll('select[data-field="nama_peternak"],[data-field*="strain"] select,[data-field*="pakan"] select').forEach(s=>s.selectedIndex=0);
            document.querySelectorAll('[id^="namaFarmToggle_"]').forEach(b=>{
                const id=b.id.split('_')[1];
                document.getElementById(`selectedFarmText_${id}`).textContent='-- Pilih Nama Farm --';
                document.getElementById(`namaFarmHidden_${id}`).value='';
                b.classList.remove('selected-farm');
                closeFarm(id);
            });
            toggle(true,tipe);
            
            document.getElementById('loading').style.display='block';
            document.getElementById('submitBtn').disabled=true;
            const x=new XMLHttpRequest();
            x.open('POST','<?=base_url('Visiting_Pedaging_Controller/get_options_by_livestock_type')?>',true);
            x.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            x.onreadystatechange=()=>{
                if(x.readyState===4&&x.status===200){
                    document.getElementById('loading').style.display='none';
                    document.getElementById('submitBtn').disabled=false;
                    const js=document.querySelector('select[onchange*="changeTipe"]');
                    if(js&&js.value!==tipe)js.value=tipe;
                }
            };
            x.send('livestock_type='+encodeURIComponent(tipe));
        };
        
        document.addEventListener('DOMContentLoaded',()=>{
            initResize();
            toggle(false);
            document.addEventListener('click',e=>{if(!e.target.closest('.custom-dropdown'))closeFarm()});
            document.getElementById('pulletForm').addEventListener('submit',()=>document.querySelectorAll('.number-format,.integer-field').forEach(i=>i.value=i.value.replace(/,/g,'')));
        });
        window.addEventListener('resize',()=>document.querySelectorAll('.auto-resize-textarea').forEach(autoResize));
    </script>
</body>
</html>
