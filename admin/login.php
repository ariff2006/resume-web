<?php
require_once '_auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pw = $_POST['password'] ?? '';
    if (hash_equals(admin_password(), $pw)) {
        $_SESSION['admin_authed'] = true;
        session_regenerate_id(true);
        header('Location: index.php');
        exit;
    }
    $error = 'รหัสผ่านไม่ถูกต้อง';
    // small delay to slow brute force
    usleep(500000);
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <title>Admin Login — Patiwat Resume</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Sans+Thai:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Inter', 'Noto Sans Thai', sans-serif;
      background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
      min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px;
    }
    .login-box {
      background: white; padding: 40px; border-radius: 16px;
      box-shadow: 0 20px 50px rgba(37,99,235,0.15); max-width: 380px; width: 100%;
    }
    .logo { text-align: center; font-size: 32px; margin-bottom: 8px; }
    h1 { text-align: center; font-size: 20px; font-weight: 600; margin-bottom: 4px; color: #1a1a1a; }
    .sub { text-align: center; color: #6b7280; font-size: 13px; margin-bottom: 24px; }
    .form-field { margin-bottom: 16px; }
    label { display: block; font-size: 13px; color: #6b7280; margin-bottom: 6px; font-weight: 500; }
    input {
      width: 100%; padding: 10px 14px; font-size: 15px;
      border: 1px solid #e5e7eb; border-radius: 8px; outline: none;
      font-family: inherit; transition: all 0.2s;
    }
    input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
    button {
      width: 100%; padding: 12px; background: #2563eb; color: white;
      border: 0; border-radius: 8px; font-size: 15px; font-weight: 600;
      cursor: pointer; transition: background 0.2s; font-family: inherit;
    }
    button:hover { background: #1d4ed8; }
    .error { background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; text-align: center; }
    .back { display: block; text-align: center; margin-top: 16px; color: #6b7280; font-size: 13px; text-decoration: none; }
    .back:hover { color: #2563eb; }
  </style>
</head>
<body>
  <div class="login-box">
    <div class="logo">🔒</div>
    <h1>Admin Login</h1>
    <p class="sub">Patiwat Resume — Admin Panel</p>
    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" autocomplete="off">
      <div class="form-field">
        <label for="pw">Password</label>
        <input type="password" id="pw" name="password" required autofocus />
      </div>
      <button type="submit">เข้าสู่ระบบ</button>
    </form>
    <a href="../" class="back">← กลับหน้า Resume</a>
  </div>
</body>
</html>
