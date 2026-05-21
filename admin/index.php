<?php
require_once '_auth.php';
require_login();

$data = load_data();
if ($data === null) {
    echo 'resume-data.json not found';
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Panel — Patiwat Resume</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Sans+Thai:wght@400;500;600;700&family=Noto+Sans+SC:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    :root { --bg:#f5f7fb; --surf:#fff; --txt:#1a1a1a; --muted:#6b7280; --border:#e5e7eb; --accent:#2563eb; --accent-soft:#eff6ff; --danger:#dc2626; --danger-soft:#fee2e2; --ok:#16a34a; }
    * { box-sizing: border-box; margin:0; padding:0; }
    body { font-family: 'Inter','Noto Sans Thai','Noto Sans SC',sans-serif; background:var(--bg); color:var(--txt); font-size:14px; line-height:1.5; }
    .topbar { background:var(--surf); border-bottom:1px solid var(--border); padding:12px 24px; display:flex; justify-content:space-between; align-items:center; position:sticky; top:0; z-index:50; }
    .topbar h1 { font-size:18px; font-weight:600; }
    .topbar .right { display:flex; gap:8px; align-items:center; }
    .btn { padding:8px 14px; border:1px solid var(--border); background:var(--surf); border-radius:8px; cursor:pointer; font-size:13px; font-weight:500; color:var(--txt); text-decoration:none; display:inline-flex; align-items:center; gap:6px; font-family:inherit; transition:all .15s; }
    .btn:hover { border-color:var(--accent); color:var(--accent); }
    .btn-primary { background:var(--accent); color:#fff; border-color:var(--accent); }
    .btn-primary:hover { background:#1d4ed8; color:#fff; }
    .btn-danger { background:var(--danger-soft); color:var(--danger); border-color:var(--danger-soft); }
    .btn-danger:hover { background:var(--danger); color:#fff; border-color:var(--danger); }
    .btn-sm { padding:4px 8px; font-size:12px; }
    .container { max-width:1100px; margin:0 auto; padding:24px; }
    .card { background:var(--surf); border:1px solid var(--border); border-radius:12px; padding:20px 24px; margin-bottom:16px; }
    .card-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; }
    .card-title { font-size:15px; font-weight:600; }
    .row { display:grid; grid-template-columns:160px 1fr; gap:12px 24px; padding:8px 0; border-bottom:1px solid var(--border); align-items:start; }
    .row:last-child { border-bottom:0; }
    .row-label { color:var(--muted); font-size:13px; }
    .row-val { font-size:14px; }
    .lang-flag { display:inline-block; background:var(--accent-soft); color:var(--accent); padding:1px 6px; border-radius:4px; font-size:11px; font-weight:600; margin-right:6px; }
    .item-list .item-row { display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-bottom:1px solid var(--border); gap:12px; }
    .item-list .item-row:last-child { border-bottom:0; }
    .item-info { flex:1; min-width:0; }
    .item-info-title { font-weight:600; }
    .item-info-sub { color:var(--muted); font-size:12px; margin-top:2px; }
    .actions { display:flex; gap:6px; flex-shrink:0; }
    .tag { background:var(--accent-soft); color:var(--accent); padding:2px 8px; border-radius:4px; font-size:11px; margin-right:4px; display:inline-block; margin-bottom:2px; }

    /* Modal */
    .modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:100; display:none; align-items:center; justify-content:center; padding:20px; }
    .modal-overlay.open { display:flex; }
    .modal { background:#fff; border-radius:12px; width:100%; max-width:720px; max-height:90vh; display:flex; flex-direction:column; }
    .modal-head { padding:18px 24px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; }
    .modal-head h2 { font-size:16px; font-weight:600; }
    .modal-close { background:none; border:0; cursor:pointer; font-size:22px; color:var(--muted); padding:4px; }
    .modal-body { padding:20px 24px; overflow-y:auto; flex:1; }
    .modal-foot { padding:14px 24px; border-top:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; gap:8px; }

    /* Tabs */
    .lang-tabs { display:flex; gap:4px; margin-bottom:16px; border-bottom:1px solid var(--border); }
    .lang-tab { padding:8px 16px; cursor:pointer; border:0; background:none; font-size:13px; font-weight:500; color:var(--muted); border-bottom:2px solid transparent; font-family:inherit; }
    .lang-tab.active { color:var(--accent); border-bottom-color:var(--accent); }

    .lang-pane { display:none; }
    .lang-pane.active { display:block; }

    .field { margin-bottom:14px; }
    .field label { display:block; font-size:12px; color:var(--muted); margin-bottom:4px; font-weight:500; }
    .field input, .field textarea, .field select {
      width:100%; padding:8px 12px; border:1px solid var(--border); border-radius:8px;
      font-size:14px; font-family:inherit; outline:none; transition:all .15s; background:#fff; color:var(--txt);
    }
    .field input:focus, .field textarea:focus { border-color:var(--accent); box-shadow:0 0 0 3px rgba(37,99,235,0.1); }
    .field textarea { resize:vertical; min-height:80px; }
    .field-hint { font-size:11px; color:var(--muted); margin-top:4px; }
    .grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
    @media (max-width:640px) { .grid-2 { grid-template-columns:1fr; } .row { grid-template-columns:1fr; gap:4px; } }

    .toast { position:fixed; bottom:24px; right:24px; background:var(--ok); color:#fff; padding:12px 20px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.15); font-size:13px; z-index:200; opacity:0; transform:translateY(20px); transition:all .3s; pointer-events:none; }
    .toast.show { opacity:1; transform:translateY(0); }
    .toast.error { background:var(--danger); }
  </style>
</head>
<body>
  <div class="topbar">
    <h1>🛠️ Admin Panel — Patiwat Resume</h1>
    <div class="right">
      <a href="../" target="_blank" class="btn">👁️ ดู Resume</a>
      <a href="logout.php" class="btn">ออกจากระบบ</a>
    </div>
  </div>

  <div class="container">

    <!-- ============ Personal Info ============ -->
    <div class="card">
      <div class="card-head">
        <div class="card-title">👤 ข้อมูลส่วนตัว</div>
        <button class="btn btn-sm" onclick="openPersonalModal()">✏️ แก้ไข</button>
      </div>
      <div class="row">
        <div class="row-label">รูปโปรไฟล์</div>
        <div class="row-val">
          <?php $photo = $data['personal']['photo'] ?? ''; ?>
          <?php if ($photo): ?>
            <img src="../<?= htmlspecialchars($photo) ?>" style="width:80px;height:80px;border-radius:50%;object-fit:cover;object-position:center top;border:2px solid #e5e7eb;" />
          <?php else: ?>
            <span style="color:var(--muted);font-size:13px;">— ยังไม่ได้อัปโหลด —</span>
          <?php endif; ?>
        </div>
      </div>
      <div class="row"><div class="row-label">Email</div><div class="row-val"><?= htmlspecialchars($data['personal']['email']) ?></div></div>
      <div class="row"><div class="row-label">Phone</div><div class="row-val"><?= htmlspecialchars($data['personal']['phone']) ?></div></div>
      <?php foreach (['th'=>'ไทย','en'=>'EN','zh'=>'中文'] as $code => $name): ?>
        <div class="row">
          <div class="row-label"><span class="lang-flag"><?= $name ?></span>ชื่อ</div>
          <div class="row-val"><?= htmlspecialchars($data['personal']['translations'][$code]['name'] ?? '') ?></div>
        </div>
        <div class="row">
          <div class="row-label"><span class="lang-flag"><?= $name ?></span>ตำแหน่ง</div>
          <div class="row-val"><?= htmlspecialchars($data['personal']['translations'][$code]['title'] ?? '') ?></div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- ============ Experience ============ -->
    <div class="card">
      <div class="card-head">
        <div class="card-title">💼 ประสบการณ์ทำงาน (<?= count($data['experience']) ?>)</div>
        <button class="btn btn-primary btn-sm" onclick="openExpModal('new')">+ เพิ่มประสบการณ์</button>
      </div>
      <div class="item-list">
        <?php foreach ($data['experience'] as $i => $exp): $t = $exp['translations']['th']; ?>
          <div class="item-row">
            <div class="item-info">
              <div class="item-info-title"><?= htmlspecialchars($t['title']) ?></div>
              <div class="item-info-sub"><?= htmlspecialchars($t['org']) ?> · <?= htmlspecialchars($t['meta']) ?></div>
            </div>
            <div class="actions">
              <?php if ($i > 0): ?><button class="btn btn-sm" onclick="moveExp('<?= $exp['id'] ?>','up')">↑</button><?php endif; ?>
              <?php if ($i < count($data['experience']) - 1): ?><button class="btn btn-sm" onclick="moveExp('<?= $exp['id'] ?>','down')">↓</button><?php endif; ?>
              <button class="btn btn-sm" onclick="openExpModal('<?= $exp['id'] ?>')">✏️</button>
              <button class="btn btn-sm btn-danger" onclick="deleteItem('experience','<?= $exp['id'] ?>')">🗑️</button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- ============ Education ============ -->
    <div class="card">
      <div class="card-head">
        <div class="card-title">🎓 การศึกษา (<?= count($data['education']) ?>)</div>
        <button class="btn btn-primary btn-sm" onclick="openEduModal('new')">+ เพิ่ม</button>
      </div>
      <div class="item-list">
        <?php foreach ($data['education'] as $edu): $t = $edu['translations']['th']; ?>
          <div class="item-row">
            <div class="item-info">
              <div class="item-info-title"><?= htmlspecialchars($t['title']) ?></div>
              <div class="item-info-sub"><?= htmlspecialchars($t['org']) ?> · <?= htmlspecialchars($t['meta']) ?></div>
            </div>
            <div class="actions">
              <button class="btn btn-sm" onclick="openEduModal('<?= $edu['id'] ?>')">✏️</button>
              <button class="btn btn-sm btn-danger" onclick="deleteItem('education','<?= $edu['id'] ?>')">🗑️</button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- ============ Skills ============ -->
    <div class="card">
      <div class="card-head">
        <div class="card-title">⚡ ทักษะ (<?= count($data['skills']) ?>)</div>
        <button class="btn btn-primary btn-sm" onclick="openSkillModal('new')">+ เพิ่มหมวด</button>
      </div>
      <div class="item-list">
        <?php foreach ($data['skills'] as $sk): $t = $sk['translations']['th']; ?>
          <div class="item-row">
            <div class="item-info">
              <div class="item-info-title"><?= htmlspecialchars($t['label']) ?></div>
              <div class="item-info-sub">
                <?php foreach (($sk['tags'] ?? []) as $tag): ?><span class="tag"><?= htmlspecialchars($tag) ?></span><?php endforeach; ?>
              </div>
            </div>
            <div class="actions">
              <button class="btn btn-sm" onclick="openSkillModal('<?= $sk['id'] ?>')">✏️</button>
              <button class="btn btn-sm btn-danger" onclick="deleteItem('skill','<?= $sk['id'] ?>')">🗑️</button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- ============ Certifications ============ -->
    <div class="card">
      <div class="card-head">
        <div class="card-title">📜 ใบรับรอง / อบรม (<?= count($data['certs']) ?>)</div>
        <button class="btn btn-primary btn-sm" onclick="openCertModal('new')">+ เพิ่ม</button>
      </div>
      <div class="item-list">
        <?php foreach ($data['certs'] as $c): $t = $c['translations']['th']; $f = $c['file'] ?? ''; ?>
          <div class="item-row">
            <div class="item-info">
              <div class="item-info-title">
                <?= htmlspecialchars($t['name']) ?>
                <?php if ($f): ?><span style="background:#d1fae5;color:#065f46;padding:1px 6px;border-radius:4px;font-size:11px;margin-left:6px;">📎 มีไฟล์</span><?php endif; ?>
              </div>
              <div class="item-info-sub">
                <?= htmlspecialchars($t['org']) ?>
                <?php if ($f): ?> · <a href="../<?= htmlspecialchars($f) ?>" target="_blank" style="color:var(--accent);">ดูไฟล์</a><?php endif; ?>
              </div>
            </div>
            <div class="actions">
              <button class="btn btn-sm" onclick="openCertModal('<?= $c['id'] ?>')">✏️</button>
              <button class="btn btn-sm btn-danger" onclick="deleteItem('cert','<?= $c['id'] ?>')">🗑️</button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>

  <!-- Generic Modal -->
  <div class="modal-overlay" id="modal">
    <div class="modal">
      <div class="modal-head">
        <h2 id="modal-title">Edit</h2>
        <button class="modal-close" onclick="closeModal()">×</button>
      </div>
      <div class="modal-body" id="modal-body"></div>
      <div class="modal-foot">
        <span id="modal-foot-left"></span>
        <div style="display:flex; gap:8px;">
          <button class="btn" onclick="closeModal()">ยกเลิก</button>
          <button class="btn btn-primary" onclick="saveModal()">บันทึก</button>
        </div>
      </div>
    </div>
  </div>

  <div class="toast" id="toast"></div>

<script>
const DATA = <?= json_encode($data, JSON_UNESCAPED_UNICODE) ?>;
const LANGS = [['th','ไทย'],['en','EN'],['zh','中文']];

let currentSaveAction = null;

function toast(msg, isErr = false) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'toast show' + (isErr ? ' error' : '');
  setTimeout(() => t.className = 'toast', 2500);
}

function openModal(title) {
  document.getElementById('modal-title').textContent = title;
  document.getElementById('modal').classList.add('open');
  document.getElementById('modal-foot-left').textContent = '';
}
function closeModal() {
  document.getElementById('modal').classList.remove('open');
  document.getElementById('modal-body').innerHTML = '';
  currentSaveAction = null;
}
async function saveModal() {
  if (typeof currentSaveAction === 'function') await currentSaveAction();
}

function langTabsHtml(formId) {
  return `<div class="lang-tabs">
    ${LANGS.map(([code, name], i) => `<button class="lang-tab${i===0?' active':''}" onclick="switchTab('${formId}','${code}',event)">${name}</button>`).join('')}
  </div>`;
}
function switchTab(formId, code, ev) {
  ev.preventDefault();
  document.querySelectorAll('#'+formId+' .lang-tab').forEach(b => b.classList.remove('active'));
  ev.target.classList.add('active');
  document.querySelectorAll('#'+formId+' .lang-pane').forEach(p => p.classList.toggle('active', p.dataset.lang === code));
}

function findItem(arr, id) { return arr.find(x => x.id === id); }

// ===================== Personal =====================
function openPersonalModal() {
  const p = DATA.personal;
  openModal('แก้ไขข้อมูลส่วนตัว');
  const photoSrc = p.photo ? '../' + p.photo : '';
  const body = `
    <div id="form-personal">
      <!-- Photo Upload Section -->
      <div class="field" style="text-align:center;padding:16px;background:#f9fafb;border-radius:10px;margin-bottom:18px;">
        <div id="photo-preview-wrap" style="margin-bottom:12px;">
          ${photoSrc
            ? `<img id="photo-preview" src="${photoSrc}" style="width:120px;height:120px;border-radius:50%;object-fit:cover;object-position:center top;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,0.1);" />`
            : `<div id="photo-preview" style="width:120px;height:120px;border-radius:50%;background:#e5e7eb;display:inline-flex;align-items:center;justify-content:center;font-size:48px;color:#9ca3af;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,0.05);">👤</div>`
          }
        </div>
        <div style="display:flex;gap:8px;justify-content:center;align-items:center;flex-wrap:wrap;">
          <label class="btn btn-sm" style="cursor:pointer;">
            📷 ${p.photo ? 'เปลี่ยนรูป' : 'อัปโหลดรูป'}
            <input type="file" id="photo-input" accept="image/jpeg,image/png,image/webp" style="display:none;" onchange="previewSelectedPhoto(this)" />
          </label>
          ${p.photo ? `<button type="button" class="btn btn-sm btn-danger" onclick="markPhotoForRemoval()">🗑️ ลบรูป</button>` : ''}
        </div>
        <input type="hidden" id="remove-photo" value="0" />
        <div id="photo-filename" style="font-size:11px;color:var(--muted);margin-top:6px;"></div>
      </div>

      <div class="grid-2">
        <div class="field"><label>Email</label><input type="email" name="email" value="${escapeAttr(p.email)}" /></div>
        <div class="field"><label>Phone</label><input type="text" name="phone" value="${escapeAttr(p.phone)}" /></div>
      </div>
      ${langTabsHtml('form-personal')}
      ${LANGS.map(([code, name], i) => {
        const tr = p.translations[code] || {};
        return `
          <div class="lang-pane${i===0?' active':''}" data-lang="${code}">
            <div class="field"><label>ชื่อ-นามสกุล (${name})</label><input type="text" name="tr[${code}][name]" value="${escapeAttr(tr.name)}" /></div>
            <div class="field"><label>ตำแหน่ง (${name})</label><input type="text" name="tr[${code}][title]" value="${escapeAttr(tr.title)}" /></div>
            <div class="field"><label>ที่อยู่ (${name})</label><input type="text" name="tr[${code}][location]" value="${escapeAttr(tr.location)}" /></div>
            <div class="field"><label>วันเกิด (${name})</label><input type="text" name="tr[${code}][dob]" value="${escapeAttr(tr.dob)}" /></div>
            <div class="field"><label>สรุปเกี่ยวกับฉัน (${name})</label><textarea rows="5" name="tr[${code}][summary]">${escapeText(tr.summary)}</textarea></div>
          </div>
        `;
      }).join('')}
    </div>`;
  document.getElementById('modal-body').innerHTML = body;

  currentSaveAction = async () => {
    const form = document.getElementById('form-personal');
    const fd = new FormData();
    fd.append('action', 'save_personal');
    fd.append('email', form.querySelector('[name=email]').value);
    fd.append('phone', form.querySelector('[name=phone]').value);
    LANGS.forEach(([code]) => {
      ['name','title','location','dob','summary'].forEach(k => {
        fd.append(`translations[${code}][${k}]`, form.querySelector(`[name="tr[${code}][${k}]"]`).value);
      });
    });
    // Photo
    const photoInput = document.getElementById('photo-input');
    if (photoInput && photoInput.files && photoInput.files[0]) {
      fd.append('photo', photoInput.files[0]);
    }
    const removeFlag = document.getElementById('remove-photo');
    if (removeFlag) fd.append('remove_photo', removeFlag.value);

    const r = await fetch('api.php', { method:'POST', body: fd }).then(r => r.json());
    if (r.ok) { toast('บันทึกเรียบร้อย ✓'); setTimeout(() => location.reload(), 600); }
    else toast(r.error || 'เกิดข้อผิดพลาด', true);
  };
}

function previewSelectedPhoto(input) {
  if (!input.files || !input.files[0]) return;
  const f = input.files[0];
  // 5MB limit
  if (f.size > 5 * 1024 * 1024) {
    toast('ไฟล์ใหญ่เกิน 5MB', true);
    input.value = '';
    return;
  }
  const url = URL.createObjectURL(f);
  document.getElementById('photo-preview-wrap').innerHTML =
    `<img id="photo-preview" src="${url}" style="width:120px;height:120px;border-radius:50%;object-fit:cover;object-position:center top;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,0.1);" />`;
  document.getElementById('photo-filename').textContent = '📂 ' + f.name;
  // If user picks new photo, clear the "remove" flag
  document.getElementById('remove-photo').value = '0';
}

function markPhotoForRemoval() {
  if (!confirm('ลบรูปโปรไฟล์เมื่อบันทึก?')) return;
  document.getElementById('remove-photo').value = '1';
  document.getElementById('photo-preview-wrap').innerHTML =
    `<div style="width:120px;height:120px;border-radius:50%;background:#fee2e2;display:inline-flex;align-items:center;justify-content:center;font-size:48px;color:#dc2626;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,0.05);">🗑️</div>`;
  document.getElementById('photo-filename').textContent = 'รูปจะถูกลบเมื่อบันทึก';
  // Clear file input too
  const input = document.getElementById('photo-input');
  if (input) input.value = '';
}

// ===================== Experience =====================
function openExpModal(id) {
  const isNew = id === 'new';
  const exp = isNew ? { id:'new', translations:{ th:{}, en:{}, zh:{} } } : findItem(DATA.experience, id);
  openModal(isNew ? 'เพิ่มประสบการณ์ทำงาน' : 'แก้ไขประสบการณ์ทำงาน');

  const body = `
    <div id="form-exp" data-id="${exp.id}">
      ${langTabsHtml('form-exp')}
      ${LANGS.map(([code, name], i) => {
        const tr = exp.translations[code] || {};
        const bullets = (tr.bullets || []).join('\n');
        return `
          <div class="lang-pane${i===0?' active':''}" data-lang="${code}">
            <div class="field"><label>ตำแหน่ง (${name})</label><input type="text" name="tr[${code}][title]" value="${escapeAttr(tr.title)}" /></div>
            <div class="field"><label>บริษัท (${name})</label><input type="text" name="tr[${code}][org]" value="${escapeAttr(tr.org)}" /></div>
            <div class="field"><label>ช่วงเวลา (${name})</label><input type="text" name="tr[${code}][meta]" value="${escapeAttr(tr.meta)}" placeholder="${code==='th'?'พ.ย. 2566 – ปัจจุบัน':code==='en'?'Nov 2023 – Present':'2023年11月 – 至今'}" /></div>
            <div class="field">
              <label>หน้าที่/ผลงาน (${name}) — บรรทัดละ 1 ข้อ</label>
              <textarea rows="8" name="tr[${code}][bullets]">${escapeText(bullets)}</textarea>
              <div class="field-hint">
                💡 บรรทัดละ 1 ข้อ — ขึ้นต้นด้วย:<br>
                <strong>1. , 2. , 3.</strong> = หัวข้อ (ตัวหนา) &nbsp;|&nbsp;
                <strong>-</strong> หรือ <strong>•</strong> = bullet ปกติ
              </div>
            </div>
            <div class="field">
              <label>ผลงานเด่น / Key Result (${name})</label>
              <textarea rows="12" name="tr[${code}][highlight]" style="font-family:'Consolas','Noto Sans Thai',monospace; font-size:13px;">${escapeText(tr.highlight)}</textarea>
              <div class="field-hint">
                💡 บรรทัดละ 1 ข้อ จะแสดงเป็น list สวย ๆ — symbol ต้นบรรทัดที่รองรับ:<br>
                <strong>✓</strong> = เครื่องหมายถูก (เขียว) &nbsp;|&nbsp;
                <strong>➤</strong> = ลูกศร (น้ำเงิน) &nbsp;|&nbsp;
                <strong>🔹</strong> = หัวข้อย่อย<br>
                <strong>★</strong> = headline &nbsp;|&nbsp;
                <strong>1. 2. 3.</strong> = หัวข้อตัวเลข &nbsp;|&nbsp;
                <strong>•</strong> = sub-bullet &nbsp;|&nbsp;
                <strong>━━━</strong> = เส้นคั่น
              </div>
            </div>
          </div>
        `;
      }).join('')}
    </div>`;
  document.getElementById('modal-body').innerHTML = body;

  currentSaveAction = async () => {
    const form = document.getElementById('form-exp');
    const fd = new FormData();
    fd.append('action', 'save_experience');
    fd.append('id', form.dataset.id);
    LANGS.forEach(([code]) => {
      ['title','org','meta','bullets','highlight'].forEach(k => {
        fd.append(`translations[${code}][${k}]`, form.querySelector(`[name="tr[${code}][${k}]"]`).value);
      });
    });
    const r = await fetch('api.php', { method:'POST', body: fd }).then(r => r.json());
    if (r.ok) { toast('บันทึกเรียบร้อย ✓'); setTimeout(() => location.reload(), 600); }
    else toast(r.error || 'เกิดข้อผิดพลาด', true);
  };
}

async function moveExp(id, dir) {
  const fd = new FormData();
  fd.append('action', 'move_experience');
  fd.append('id', id);
  fd.append('dir', dir);
  await fetch('api.php', { method:'POST', body: fd });
  location.reload();
}

// ===================== Education =====================
function openEduModal(id) {
  const isNew = id === 'new';
  const edu = isNew ? { id:'new', translations:{ th:{}, en:{}, zh:{} } } : findItem(DATA.education, id);
  openModal(isNew ? 'เพิ่มการศึกษา' : 'แก้ไขการศึกษา');

  const body = `
    <div id="form-edu" data-id="${edu.id}">
      ${langTabsHtml('form-edu')}
      ${LANGS.map(([code, name], i) => {
        const tr = edu.translations[code] || {};
        return `
          <div class="lang-pane${i===0?' active':''}" data-lang="${code}">
            <div class="field"><label>วุฒิ/สาขา (${name})</label><input type="text" name="tr[${code}][title]" value="${escapeAttr(tr.title)}" /></div>
            <div class="field"><label>สถาบัน (${name})</label><input type="text" name="tr[${code}][org]" value="${escapeAttr(tr.org)}" /></div>
            <div class="field"><label>ช่วงเวลา / GPA (${name})</label><input type="text" name="tr[${code}][meta]" value="${escapeAttr(tr.meta)}" /></div>
          </div>
        `;
      }).join('')}
    </div>`;
  document.getElementById('modal-body').innerHTML = body;

  currentSaveAction = async () => {
    const form = document.getElementById('form-edu');
    const fd = new FormData();
    fd.append('action', 'save_education');
    fd.append('id', form.dataset.id);
    LANGS.forEach(([code]) => {
      ['title','org','meta'].forEach(k => {
        fd.append(`translations[${code}][${k}]`, form.querySelector(`[name="tr[${code}][${k}]"]`).value);
      });
    });
    const r = await fetch('api.php', { method:'POST', body: fd }).then(r => r.json());
    if (r.ok) { toast('บันทึกเรียบร้อย ✓'); setTimeout(() => location.reload(), 600); }
    else toast(r.error || 'เกิดข้อผิดพลาด', true);
  };
}

// ===================== Skills =====================
function openSkillModal(id) {
  const isNew = id === 'new';
  const sk = isNew ? { id:'new', tags:[], translations:{ th:{}, en:{}, zh:{} } } : findItem(DATA.skills, id);
  openModal(isNew ? 'เพิ่มหมวดทักษะ' : 'แก้ไขหมวดทักษะ');

  const body = `
    <div id="form-skill" data-id="${sk.id}">
      <div class="field">
        <label>Tags (คั่นด้วยจุลภาค ,)</label>
        <textarea rows="3" name="tags">${escapeText((sk.tags || []).join(', '))}</textarea>
        <div class="field-hint">ตัวอย่าง: Cisco, Fortigate, VLAN, Switch</div>
      </div>
      ${langTabsHtml('form-skill')}
      ${LANGS.map(([code, name], i) => {
        const tr = sk.translations[code] || {};
        return `
          <div class="lang-pane${i===0?' active':''}" data-lang="${code}">
            <div class="field"><label>ชื่อหมวด (${name})</label><input type="text" name="tr[${code}][label]" value="${escapeAttr(tr.label)}" placeholder="${code==='th'?'เช่น Network':code==='en'?'e.g. Network':'例如:网络'}" /></div>
          </div>
        `;
      }).join('')}
    </div>`;
  document.getElementById('modal-body').innerHTML = body;

  currentSaveAction = async () => {
    const form = document.getElementById('form-skill');
    const fd = new FormData();
    fd.append('action', 'save_skill');
    fd.append('id', form.dataset.id);
    fd.append('tags', form.querySelector('[name=tags]').value);
    LANGS.forEach(([code]) => {
      fd.append(`translations[${code}][label]`, form.querySelector(`[name="tr[${code}][label]"]`).value);
    });
    const r = await fetch('api.php', { method:'POST', body: fd }).then(r => r.json());
    if (r.ok) { toast('บันทึกเรียบร้อย ✓'); setTimeout(() => location.reload(), 600); }
    else toast(r.error || 'เกิดข้อผิดพลาด', true);
  };
}

// ===================== Certifications =====================
function openCertModal(id) {
  const isNew = id === 'new';
  const c = isNew ? { id:'new', translations:{ th:{}, en:{}, zh:{} }, file:'' } : findItem(DATA.certs, id);
  const existingFile = c.file || '';
  openModal(isNew ? 'เพิ่มใบรับรอง' : 'แก้ไขใบรับรอง');

  // File preview block
  const filePreview = existingFile
    ? `<div style="background:#f0f9ff;border:1px solid #bae6fd;padding:10px 12px;border-radius:8px;margin-bottom:12px;font-size:13px;">
         📎 ไฟล์ปัจจุบัน: <a href="../${escapeAttr(existingFile)}" target="_blank" style="color:#2563eb;font-weight:500;">${escapeAttr(existingFile.replace('certs/',''))}</a>
         <button type="button" onclick="clearCertFile()" style="background:#fee2e2;color:#991b1b;border:0;padding:3px 10px;border-radius:6px;cursor:pointer;font-size:12px;margin-left:8px;">ลบไฟล์</button>
         <input type="hidden" id="cert-keep-file" value="${escapeAttr(existingFile)}" />
       </div>`
    : `<input type="hidden" id="cert-keep-file" value="" />`;

  const body = `
    <div id="form-cert" data-id="${c.id}">
      ${filePreview}
      <div class="field">
        <label>📎 อัปโหลดใบประกาศ (jpg/png/pdf — สูงสุด 10MB)</label>
        <input type="file" id="cert-file-input" accept=".jpg,.jpeg,.png,.pdf,.webp" />
        <div class="field-hint">เลือกไฟล์ใหม่เพื่อ "แทนที่" ไฟล์เดิม • ถ้าไม่เลือก = ใช้ไฟล์เดิม</div>
      </div>
      ${langTabsHtml('form-cert')}
      ${LANGS.map(([code, name], i) => {
        const tr = c.translations[code] || {};
        return `
          <div class="lang-pane${i===0?' active':''}" data-lang="${code}">
            <div class="field"><label>ชื่อใบรับรอง (${name})</label><input type="text" name="tr[${code}][name]" value="${escapeAttr(tr.name)}" /></div>
            <div class="field"><label>สถาบัน / ปี (${name})</label><input type="text" name="tr[${code}][org]" value="${escapeAttr(tr.org)}" /></div>
          </div>
        `;
      }).join('')}
    </div>`;
  document.getElementById('modal-body').innerHTML = body;

  currentSaveAction = async () => {
    const form = document.getElementById('form-cert');
    const fd = new FormData();
    fd.append('action', 'save_cert');
    fd.append('id', form.dataset.id);
    LANGS.forEach(([code]) => {
      ['name','org'].forEach(k => {
        fd.append(`translations[${code}][${k}]`, form.querySelector(`[name="tr[${code}][${k}]"]`).value);
      });
    });
    // Append file if user picked one
    const fileInput = document.getElementById('cert-file-input');
    if (fileInput && fileInput.files && fileInput.files[0]) {
      fd.append('cert_file', fileInput.files[0]);
    }
    // Keep file flag (if user didn't click "ลบไฟล์")
    const keep = document.getElementById('cert-keep-file');
    if (keep) fd.append('keep_file', keep.value);

    const r = await fetch('api.php', { method:'POST', body: fd }).then(r => r.json());
    if (r.ok) { toast('บันทึกเรียบร้อย ✓'); setTimeout(() => location.reload(), 600); }
    else toast(r.error || 'เกิดข้อผิดพลาด', true);
  };
}

function clearCertFile() {
  const keep = document.getElementById('cert-keep-file');
  if (keep) keep.value = '';
  // Visually hide the existing file preview
  const preview = keep.parentElement;
  if (preview) preview.style.display = 'none';
  toast('ไฟล์จะถูกลบเมื่อบันทึก');
}

// ===================== Delete =====================
async function deleteItem(type, id) {
  if (!confirm('ยืนยันการลบ?')) return;
  const fd = new FormData();
  fd.append('action', 'delete_' + type);
  fd.append('id', id);
  const r = await fetch('api.php', { method:'POST', body: fd }).then(r => r.json());
  if (r.ok) { toast('ลบเรียบร้อย ✓'); setTimeout(() => location.reload(), 500); }
  else toast(r.error || 'เกิดข้อผิดพลาด', true);
}

// ===================== Utils =====================
function escapeAttr(s) { return (s == null ? '' : String(s)).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function escapeText(s) { return (s == null ? '' : String(s)).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

// Close modal on backdrop click or Esc
document.getElementById('modal').addEventListener('click', e => {
  if (e.target.id === 'modal') closeModal();
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
</script>
</body>
</html>
