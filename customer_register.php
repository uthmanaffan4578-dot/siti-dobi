<?php
$conn = new mysqli("localhost", "root", "", "siti_dobi_db");

if ($conn->connect_error) {
    die("Sambungan gagal: " . $conn->connect_error);
}

if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $phone = preg_replace('/[^0-9]/', '', $_POST['phone']); // Buang sengkang/ruang supaya format telefon konsisten
    $email = !empty($_POST['email']) ? trim($_POST['email']) : NULL;
    $password = $_POST['password'];

    if (strlen($phone) < 10 || strlen($phone) > 12) {
        echo "<script>alert('Ralat: No telefon tidak sah!');</script>";
    } elseif (strlen($password) < 6) {
        echo "<script>alert('Ralat: Kata laluan mesti sekurang-kurangnya 6 aksara!');</script>";
    } else {
        // KESELAMATAN: Simpan hash sahaja, bukan kata laluan sebenar
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // 1. Masukkan data ke dalam jadual customer (prepared statement elak SQL injection)
        // Nota: Trigger 'trg_create_loyalty_card' dalam SQL korang akan auto-create kad jajaran kesetiaan secara automatik!
        $stmt = $conn->prepare("INSERT INTO customer (name, phone, email, password_hash) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $phone, $email, $password_hash);

        // Nota: Sejak PHP 8.1, mysqli baling exception bila query gagal, jadi kena guna try/catch
        try {
            $stmt->execute();
            echo "<script>alert('Akaun pelanggan berjaya dicipta! Sila gunakan no. telefon & password tersebut untuk login.'); window.location='customer_login.php';</script>";
            exit();
        } catch (mysqli_sql_exception $e) {
            // Jika no telefon dah wujud (sebab ada UNIQUE KEY pada phone dalam database korang)
            if ($e->getCode() == 1062) {
                echo "<script>alert('Ralat: No telefon ini sudah berdaftar dengan sistem!');</script>";
            } else {
                echo "<script>alert('Ralat sistem. Sila cuba lagi.');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Siti Dobi - Daftar Akaun Pelanggan</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .box { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); width: 100%; max-width: 400px; }
        h2 { color: #0f172a; margin-bottom: 20px; text-align: center; }
        .group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #64748b; font-size: 0.9rem; }
        input { width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 1rem; box-sizing: border-box; }
        .btn { background-color: #0284c7; color: white; border: none; padding: 12px; width: 100%; border-radius: 8px; cursor: pointer; font-weight: 500; font-size: 1rem; margin-top: 10px; }
        .link { display: block; text-align: center; margin-top: 15px; color: #64748b; text-decoration: none; font-size: 0.9rem; }
        .link:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="box">
    <h2>Daftar Akaun Pelanggan</h2>
    <form action="customer_register.php" method="POST">
        <div class="group">
            <label>Nama Penuh</label>
            <input type="text" name="name" placeholder="Contoh: Ali bin Abu" required>
        </div>
        <div class="group">
            <label>No. Telefon</label>
            <input type="text" name="phone" placeholder="Contoh: 0111234567" required>
        </div>
        <div class="group">
            <label>Email (Optional / Boleh Kosong)</label>
            <input type="email" name="email" placeholder="Contoh: ali@email.com">
        </div>
        <div class="group">
            <label>Kata Laluan Portal</label>
            <input type="password" name="password" placeholder="Cipta kata laluan anda" required>
        </div>
        <button type="submit" name="register" class="btn">Cipta Akaun Baru</button>
        <a href="customer_login.php" class="link">Dah ada akaun? Balik ke login</a>
    </form>
</div>
</body>
</html>