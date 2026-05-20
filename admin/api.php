<?php
require_once '_auth.php';

if (!is_logged_in()) {
    json_response(['error' => 'Unauthorized'], 401);
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$data = load_data();
if ($data === null) {
    json_response(['error' => 'resume-data.json not found'], 500);
}

// ===== Helper: find index of item by id in an array =====
function find_idx(&$arr, $id) {
    foreach ($arr as $i => $item) {
        if (($item['id'] ?? '') === $id) return $i;
    }
    return -1;
}

// ===== Actions =====
switch ($action) {

    case 'save_personal': {
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $tr = $_POST['translations'] ?? [];
        if (!is_array($tr)) { json_response(['error' => 'bad translations'], 400); }
        $data['personal']['email'] = $email;
        $data['personal']['phone'] = $phone;
        foreach (['th','en','zh'] as $lang) {
            if (isset($tr[$lang])) {
                $data['personal']['translations'][$lang] = [
                    'name'     => trim($tr[$lang]['name'] ?? ''),
                    'title'    => trim($tr[$lang]['title'] ?? ''),
                    'location' => trim($tr[$lang]['location'] ?? ''),
                    'dob'      => trim($tr[$lang]['dob'] ?? ''),
                    'summary'  => trim($tr[$lang]['summary'] ?? ''),
                ];
            }
        }
        save_data($data);
        json_response(['ok' => true]);
    }

    case 'save_labels': {
        $tr = $_POST['labels'] ?? [];
        foreach (['th','en','zh'] as $lang) {
            if (isset($tr[$lang])) {
                foreach (['summaryTitle','experienceTitle','educationTitle','skillsTitle','certsTitle','keyResult','footer'] as $k) {
                    if (isset($tr[$lang][$k])) {
                        $data['labels'][$lang][$k] = trim($tr[$lang][$k]);
                    }
                }
            }
        }
        save_data($data);
        json_response(['ok' => true]);
    }

    case 'save_experience': {
        $id = $_POST['id'] ?? '';
        $tr = $_POST['translations'] ?? [];
        if (!is_array($tr)) { json_response(['error' => 'bad translations'], 400); }

        $clean = [];
        foreach (['th','en','zh'] as $lang) {
            $b = $tr[$lang]['bullets'] ?? '';
            // bullets come as newline-separated text; split into array
            if (is_string($b)) {
                $bullets = array_values(array_filter(array_map('trim', explode("\n", $b)), fn($x) => $x !== ''));
            } elseif (is_array($b)) {
                $bullets = array_values(array_filter(array_map('trim', $b), fn($x) => $x !== ''));
            } else {
                $bullets = [];
            }
            $clean[$lang] = [
                'title'     => trim($tr[$lang]['title'] ?? ''),
                'org'       => trim($tr[$lang]['org'] ?? ''),
                'meta'      => trim($tr[$lang]['meta'] ?? ''),
                'bullets'   => $bullets,
                'highlight' => trim($tr[$lang]['highlight'] ?? ''),
            ];
        }

        if ($id === '' || $id === 'new') {
            $newItem = ['id' => new_id('exp'), 'translations' => $clean];
            $data['experience'][] = $newItem;
            save_data($data);
            json_response(['ok' => true, 'id' => $newItem['id']]);
        } else {
            $idx = find_idx($data['experience'], $id);
            if ($idx === -1) { json_response(['error' => 'not found'], 404); }
            $data['experience'][$idx]['translations'] = $clean;
            save_data($data);
            json_response(['ok' => true]);
        }
    }

    case 'delete_experience': {
        $id = $_POST['id'] ?? '';
        $idx = find_idx($data['experience'], $id);
        if ($idx === -1) { json_response(['error' => 'not found'], 404); }
        array_splice($data['experience'], $idx, 1);
        save_data($data);
        json_response(['ok' => true]);
    }

    case 'move_experience': {
        $id = $_POST['id'] ?? '';
        $dir = $_POST['dir'] ?? 'up';
        $idx = find_idx($data['experience'], $id);
        if ($idx === -1) { json_response(['error' => 'not found'], 404); }
        $newIdx = $dir === 'up' ? $idx - 1 : $idx + 1;
        if ($newIdx < 0 || $newIdx >= count($data['experience'])) { json_response(['ok' => true]); }
        $tmp = $data['experience'][$idx];
        $data['experience'][$idx] = $data['experience'][$newIdx];
        $data['experience'][$newIdx] = $tmp;
        save_data($data);
        json_response(['ok' => true]);
    }

    case 'save_education': {
        $id = $_POST['id'] ?? '';
        $tr = $_POST['translations'] ?? [];
        $clean = [];
        foreach (['th','en','zh'] as $lang) {
            $clean[$lang] = [
                'title' => trim($tr[$lang]['title'] ?? ''),
                'org'   => trim($tr[$lang]['org']   ?? ''),
                'meta'  => trim($tr[$lang]['meta']  ?? ''),
            ];
        }
        if ($id === '' || $id === 'new') {
            $newItem = ['id' => new_id('edu'), 'translations' => $clean];
            $data['education'][] = $newItem;
            save_data($data);
            json_response(['ok' => true, 'id' => $newItem['id']]);
        } else {
            $idx = find_idx($data['education'], $id);
            if ($idx === -1) { json_response(['error' => 'not found'], 404); }
            $data['education'][$idx]['translations'] = $clean;
            save_data($data);
            json_response(['ok' => true]);
        }
    }

    case 'delete_education': {
        $id = $_POST['id'] ?? '';
        $idx = find_idx($data['education'], $id);
        if ($idx === -1) { json_response(['error' => 'not found'], 404); }
        array_splice($data['education'], $idx, 1);
        save_data($data);
        json_response(['ok' => true]);
    }

    case 'save_skill': {
        $id = $_POST['id'] ?? '';
        $tagsRaw = $_POST['tags'] ?? '';
        $tags = is_array($tagsRaw)
            ? array_values(array_filter(array_map('trim', $tagsRaw), fn($x) => $x !== ''))
            : array_values(array_filter(array_map('trim', explode(',', $tagsRaw)), fn($x) => $x !== ''));
        $tr = $_POST['translations'] ?? [];
        $clean = [];
        foreach (['th','en','zh'] as $lang) {
            $clean[$lang] = [ 'label' => trim($tr[$lang]['label'] ?? '') ];
        }
        if ($id === '' || $id === 'new') {
            $newItem = ['id' => new_id('sk'), 'tags' => $tags, 'translations' => $clean];
            $data['skills'][] = $newItem;
            save_data($data);
            json_response(['ok' => true, 'id' => $newItem['id']]);
        } else {
            $idx = find_idx($data['skills'], $id);
            if ($idx === -1) { json_response(['error' => 'not found'], 404); }
            $data['skills'][$idx]['tags'] = $tags;
            $data['skills'][$idx]['translations'] = $clean;
            save_data($data);
            json_response(['ok' => true]);
        }
    }

    case 'delete_skill': {
        $id = $_POST['id'] ?? '';
        $idx = find_idx($data['skills'], $id);
        if ($idx === -1) { json_response(['error' => 'not found'], 404); }
        array_splice($data['skills'], $idx, 1);
        save_data($data);
        json_response(['ok' => true]);
    }

    case 'save_cert': {
        $id = $_POST['id'] ?? '';
        $tr = $_POST['translations'] ?? [];
        $clean = [];
        foreach (['th','en','zh'] as $lang) {
            $clean[$lang] = [
                'name' => trim($tr[$lang]['name'] ?? ''),
                'org'  => trim($tr[$lang]['org']  ?? ''),
            ];
        }

        // === Handle file upload (optional) ===
        $uploadedFile = null;
        if (isset($_FILES['cert_file']) && $_FILES['cert_file']['error'] === UPLOAD_ERR_OK) {
            $tmp = $_FILES['cert_file']['tmp_name'];
            $origName = $_FILES['cert_file']['name'];
            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','pdf','webp'];
            if (!in_array($ext, $allowed, true)) {
                json_response(['error' => 'ไฟล์ต้องเป็น jpg, png, pdf, webp เท่านั้น'], 400);
            }
            // Sanitize filename: keep original name (without ext) but slug it
            $base = preg_replace('/[^A-Za-z0-9_-]+/', '_', pathinfo($origName, PATHINFO_FILENAME));
            $base = trim($base, '_') ?: 'cert';
            $finalName = $base . '_' . substr(md5(uniqid('', true)), 0, 6) . '.' . $ext;
            $destPath = certs_dir() . '/' . $finalName;
            if (!move_uploaded_file($tmp, $destPath)) {
                json_response(['error' => 'อัปโหลดไฟล์ไม่สำเร็จ'], 500);
            }
            @chmod($destPath, 0644);
            $uploadedFile = 'certs/' . $finalName;
        }

        // Keep existing file if no new file uploaded
        $keepFile = trim($_POST['keep_file'] ?? '');

        if ($id === '' || $id === 'new') {
            $newItem = ['id' => new_id('cert'), 'translations' => $clean];
            if ($uploadedFile) $newItem['file'] = $uploadedFile;
            $data['certs'][] = $newItem;
            save_data($data);
            json_response(['ok' => true, 'id' => $newItem['id'], 'file' => $uploadedFile]);
        } else {
            $idx = find_idx($data['certs'], $id);
            if ($idx === -1) { json_response(['error' => 'not found'], 404); }
            $data['certs'][$idx]['translations'] = $clean;

            if ($uploadedFile) {
                // Delete old file if exists and different
                $old = $data['certs'][$idx]['file'] ?? '';
                if ($old && $old !== $uploadedFile) {
                    @unlink(__DIR__ . '/../' . $old);
                }
                $data['certs'][$idx]['file'] = $uploadedFile;
            } elseif ($keepFile === '') {
                // Explicit clear (user clicked "ลบไฟล์")
                $old = $data['certs'][$idx]['file'] ?? '';
                if ($old) @unlink(__DIR__ . '/../' . $old);
                unset($data['certs'][$idx]['file']);
            }
            // Otherwise: keep existing
            save_data($data);
            json_response(['ok' => true, 'file' => $data['certs'][$idx]['file'] ?? null]);
        }
    }

    case 'delete_cert': {
        $id = $_POST['id'] ?? '';
        $idx = find_idx($data['certs'], $id);
        if ($idx === -1) { json_response(['error' => 'not found'], 404); }
        // Delete associated file
        $f = $data['certs'][$idx]['file'] ?? '';
        if ($f) @unlink(__DIR__ . '/../' . $f);
        array_splice($data['certs'], $idx, 1);
        save_data($data);
        json_response(['ok' => true]);
    }

    default:
        json_response(['error' => 'unknown action: ' . $action], 400);
}
