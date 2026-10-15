<?php
session_start();
// SEKATAN KESELAMATAN: Hanya admin yang sedang log masuk boleh daftar staf baru
if (!isset($_SESSION['staff']) || $_SESSION['staff']['role'] !== 'admin') {
    header("Location: staff_login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "siti_dobi_db");

if ($conn->connect_error) {
    die("Sambungan gagal: " . $conn->connect_error);
}

if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = ($_POST['role'] === 'admin') ? 'admin' : 'staff'; // Hanya terima nilai yang sah

    if (strlen($password) < 8) {
        echo "<script>alert('Kata laluan staf mesti sekurang-kurangnya 8 aksara!'); window.location='staff_register.php';</script>";
        exit();
    }

    // SEMAK: Pastikan username tidak bertindan dalam database
    $check = $conn->prepare("SELECT staff_id FROM staff WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo "<script>alert('Username sudah wujud! Sila pilih username lain.'); window.location='staff_register.php';</script>";
        exit();
    }

    // KESELAMATAN: Gunakan BCRYPT hash password seperti yang ada dalam database kau
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Langkah INSERT data staf baru (Default is_active = 1)
    $stmt = $conn->prepare("INSERT INTO staff (name, role, username, password_hash, created_at, is_active)
                            VALUES (?, ?, ?, ?, NOW(), 1)");
    $stmt->bind_param("ssss", $name, $role, $username, $password_hash);

    if ($stmt->execute()) {
        echo "<script>alert('Pendaftaran Staf Berjaya!'); window.location='index.php?page=staff';</script>";
        exit();
    } else {
        echo "<script>alert('Ralat sistem. Sila cuba lagi.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Siti Dobi - Daftar Staf & Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Poppins', sans-serif; 
            background-image: linear-gradient(rgba(15, 23, 42, 0.6), rgba(15, 23, 42, 0.6)), url('bgdobi.jpg'); 
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            margin: 0; 
            padding: 20px;
            box-sizing: border-box;
        }

        .box { 
            background: rgba(15, 23, 42, 0.85); 
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 35px; 
            border-radius: 16px; 
            box-shadow: 0 20px 45px rgba(0,0,0,0.5); 
            width: 100%; 
            max-width: 420px;
            box-sizing: border-box;
        }

        h2 { color: #ffffff; margin-top: 0; margin-bottom: 5px; text-align: center; font-weight: 600; font-size: 1.5rem; }
        p.subtitle { color: #94a3b8; text-align: center; font-size: 0.85rem; margin-top: 0; margin-bottom: 25px; }
        
        .group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 6px; color: #cbd5e1; font-size: 0.9rem; }
        
        input, select { 
            width: 100%; 
            padding: 11px; 
            background: #1e293b;
            border: 1px solid #334155; 
            border-radius: 8px; 
            font-size: 0.95rem; 
            color: white;
            box-sizing: border-box; 
        }
        input:focus, select:focus { outline: none; border-color: #f97316; }
        
        .btn { 
            background-color: #f97316; 
            color: white; 
            border: none; 
            padding: 12px; 
            width: 100%; 
            border-radius: 8px; 
            cursor: pointer; 
            font-weight: 600; 
            font-size: 1rem; 
            margin-top: 15px; 
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3); 
        }
        .btn:hover { background-color: #ea580c; }
        
        .link { display: block; text-align: center; margin-top: 20px; color: #94a3b8; text-decoration: none; font-size: 0.85rem; }
        .link:hover { color: #ffffff; text-decoration: underline; }
    </style>
</head>
<body>

<div class="box">
    <h2>Daftar Staf Baharu</h2>
    <p class="subtitle">Siti Dobi Management Console</p>
    
    <form action="staff_register.php" method="POST">
        <div class="group">
            <label>Nama Penuh Staf</label>
            <input type="text" name="name" placeholder="Contoh: Ahmad bin Fauzi" required>
        </div>
        
        <div class="group">
            <label>Peranan / Jawatan (Role)</label>
            <select name="role" required>
                <option value="staff">Staff Kaunter</option>
                <option value="admin">Administrator (Admin)</option>
            </select>
        </div>

        <div class="group">
            <label>ID Username Staf</label>
            <input type="text" name="username" placeholder="Contoh: amir_staff" required>
        </div>
        
        <div class="group">
            <label>Kata Laluan (Password)</label>
            <input type="password" name="password" placeholder="Masukkan kata laluan baharu" required>
        </div>
        
        <button type="submit" name="register" class="btn">Daftar Akaun Staf</button>
        <a href="index.php?page=staff" class="link">← Kembali ke Pengurusan Staf</a>
    </form>
</div>

</body>
</html>