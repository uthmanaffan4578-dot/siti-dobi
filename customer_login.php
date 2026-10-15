<?php
session_start();
$conn = new mysqli("localhost", "root", "", "siti_dobi_db");

if ($conn->connect_error) {
    die("Sambungan gagal: " . $conn->connect_error);
}

if (isset($_POST['login'])) {
    $phone = preg_replace('/[^0-9]/', '', $_POST['phone']);
    $password = $_POST['password'];

    // Look up by phone number (unique), then verify the password hash
    $stmt = $conn->prepare("SELECT * FROM customer WHERE phone = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $customer = $stmt->get_result()->fetch_assoc();

    if ($customer && password_verify($password, $customer['password_hash'])) {
        unset($customer['password_hash']); // Never keep the hash in the session
        session_regenerate_id(true);
        $_SESSION['customer'] = $customer;
        echo "<script>window.location='customer_dashboard.php';</script>";
        exit();
    } else {
        echo "<script>alert('No. telefon atau kata laluan salah!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Siti Dobi - Login Pelanggan</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Poppins', sans-serif; 
            background-image: linear-gradient(rgba(15, 23, 42, 0.55), rgba(15, 23, 42, 0.55)), url('bgdobi.jpg'); 
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            display: flex; 
            flex-direction: column; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            margin: 0; 
            padding: 20px;
            box-sizing: border-box;
        }

        .welcome-title {
            color: #ffffff;
            font-size: 3.5rem; 
            font-weight: 600;
            text-align: center;
            margin-top: 0;
            margin-bottom: 20px; 
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.7);
            width: 100%;
            max-width: 800px; 
        }

        .login-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            max-width: 400px;
        }

        .box { 
            background: white; 
            padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.3); 
            width: 100%; 
            box-sizing: border-box;
        }

        h2 { color: #0f172a; margin-bottom: 20px; text-align: center; font-weight: 600; font-size: 1.3rem; }
        .group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #64748b; font-size: 0.9rem; }
        input { width: 100%; padding: 11px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 1rem; box-sizing: border-box; }
        input:focus { outline: none; border-color: #22c55e; }
        .btn { background-color: #22c55e; color: white; border: none; padding: 12px; width: 100%; border-radius: 8px; cursor: pointer; font-weight: 500; font-size: 1rem; margin-top: 10px; box-shadow: 0 4px 12px rgba(34, 197, 94, 0.2); }
        .btn:hover { background-color: #16a34a; }
        
        .link { display: block; text-align: center; margin-top: 15px; color: #0284c7; text-decoration: none; font-size: 0.9rem; }
        .link:hover { text-decoration: underline; }

        /* About Us link, separated from the login links */
        .link-about {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #f97316; /* Brand orange */
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            border-top: 1px solid #f1f5f9; /* Thin divider above the link */
            padding-top: 15px;
        }
        .link-about:hover {
            color: #ea580c;
            text-decoration: underline;
        }
    </style>
</head>
<body>

<h1 class="welcome-title">Selamat Datang ke Siti Dobi</h1>

<div class="login-wrapper">
    <div class="box">
        <h2>Portal Login Pelanggan</h2>
        <form action="customer_login.php" method="POST">
            <div class="group">
                <label>No. Telefon</label>
                <input type="tel" name="phone" placeholder="Contoh: 0111234567" required>
            </div>
            <div class="group">
                <label>Kata Laluan</label>
                <input type="password" name="password" placeholder="Masukkan password" required>
            </div>
            <button type="submit" name="login" class="btn">Masuk Portal</button>
            
            <a href="customer_register.php" class="link">Belum ada akaun? Daftar sekarang</a>
            
            <a href="about_us.php" class="link-about">ℹ️ Learn More: About Us</a>
        </form>
    </div>
</div>

</body>
</html>