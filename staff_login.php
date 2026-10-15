<?php
session_start();
$conn = new mysqli("localhost", "root", "", "siti_dobi_db");

if ($conn->connect_error) {
    die("Sambungan gagal: " . $conn->connect_error);
}

if (isset($_POST['login_staff'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Look up the username (prepared statement prevents SQL injection)
    $stmt = $conn->prepare("SELECT * FROM staff WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $staff = $stmt->get_result()->fetch_assoc();

    // Same message for a wrong username or password, so usernames can't be guessed
    if ($staff && password_verify($password, $staff['password_hash'])) {
        if ($staff['is_active'] != 1) {
            echo "<script>alert('Akaun staf ini telah digantung. Sila hubungi admin.');</script>";
        } else {
            unset($staff['password_hash']); // Never keep the hash in the session
            session_regenerate_id(true);
            $_SESSION['staff'] = $staff;
            echo "<script>window.location='index.php';</script>";
            exit();
        }
    } else {
        echo "<script>alert('Username atau kata laluan salah!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siti Dobi - Log Masuk Staf</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Poppins', sans-serif; 
            background-image: linear-gradient(rgba(9, 13, 22, 0.8), rgba(9, 13, 22, 0.8)), url('bgdobi.jpg'); 
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
        }
        .login-box { 
            background: rgba(15, 23, 42, 0.75); 
            backdrop-filter: blur(12px);
            border: 1px solid #1e293b;
            padding: 40px; 
            border-radius: 16px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.3); 
            width: 100%; 
            max-width: 380px; 
        }
        h2 { 
            background: linear-gradient(to right, #ffffff, #38bdf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 5px; 
            text-align: center; 
            font-weight: 600;
        }
        p.subtitle {
            color: #64748b;
            text-align: center;
            font-size: 0.85rem;
            margin-bottom: 25px;
        }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 6px; color: #94a3b8; font-size: 0.85rem; }
        input { 
            width: 100%; 
            padding: 12px; 
            background: #0f172a;
            border: 1px solid #1e293b; 
            border-radius: 8px; 
            font-size: 0.95rem; 
            color: #fff;
            box-sizing: border-box; 
        }
        input:focus {
            outline: none;
            border-color: #0284c7;
        }
        .btn-login { 
            background: linear-gradient(135deg, #0284c7, #0369a1); 
            color: white; 
            border: none; 
            padding: 12px; 
            width: 100%; 
            border-radius: 8px; 
            cursor: pointer; 
            font-weight: 500; 
            font-size: 1rem; 
            margin-top: 10px; 
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);
        }
        .btn-login:hover { background: #0284c7; }
        
        /* Links below the form */
        .link-group {
            text-align: center;
            margin-top: 20px;
        }
        .link {
            display: block;
            color: #38bdf8;
            text-decoration: none;
            font-size: 0.88rem;
            margin-bottom: 8px;
            transition: color 0.2s;
        }
        .link:hover {
            color: #0284c7;
            text-decoration: underline;
        }
        .back-link {
            display: block;
            color: #64748b;
            text-decoration: none;
            font-size: 0.85rem;
        }
        .back-link:hover {
            color: #94a3b8;
        }
    </style>
</head>
<body>
<div class="login-box">
    <h2>Siti Dobi</h2>
    <p class="subtitle">Kaunter Pengurusan Staf & Admin</p>
    <form action="staff_login.php" method="POST">
        <div class="form-group">
            <label>ID Username Staf</label>
            <input type="text" name="username" placeholder="Masukkan username staf" required>
        </div>
        <div class="form-group">
            <label>Kata Laluan</label>
            <input type="password" name="password" placeholder="Masukkan kata laluan" required>
        </div>
        <button type="submit" name="login_staff" class="btn-login">Masuk Dashboard</button>
        
        <div class="link-group">
            <!-- No public sign-up: admins create staff accounts from Staff Management -->
            <a href="welcome.php" class="back-link">← Kembali ke Menu Utama</a>
        </div>
    </form>
</div>
</body>
</html>