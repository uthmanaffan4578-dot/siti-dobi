<?php
session_start();
// SEKATAN KESELAMATAN: Hanya staf yang log masuk boleh tambah transaksi
if (!isset($_SESSION['staff'])) {
    header("Location: staff_login.php");
    exit();
}

$host     = "localhost";
$username = "root";
$password = ""; 
$dbname   = "siti_dobi_db";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Sambungan gagal: " . $conn->connect_error);
}

if (isset($_POST['simpan_transaksi'])) {
    $customer_id    = intval($_POST['customer_id']);
    $staff_id       = intval($_POST['staff_id']);
    $total_amount   = floatval($_POST['total_amount']);
    $payment_method = in_array($_POST['payment_method'], ['cash', 'ewallet', 'online_transfer', 'debit_card']) ? $_POST['payment_method'] : 'cash';
    $status         = ($_POST['status'] === 'completed') ? 'completed' : 'pending';

    // Sentiasa INSERT sebagai 'pending' dulu. Trigger stamp hanya jalan bila status di-UPDATE ke 'completed'.
    $stmt = $conn->prepare("INSERT INTO transaction (customer_id, staff_id, total_amount, payment_method, status)
                            VALUES (?, ?, ?, ?, 'pending')");
    $stmt->bind_param("iids", $customer_id, $staff_id, $total_amount, $payment_method);

    if ($stmt->execute()) {
        if ($status === 'completed') {
            $new_txn_id = $conn->insert_id;
            $upd = $conn->prepare("UPDATE transaction SET status = 'completed' WHERE transaction_id = ?");
            $upd->bind_param("i", $new_txn_id);
            $upd->execute();
        }
        header("Location: index.php");
        exit();
    } else {
        echo "Ralat: Transaksi gagal disimpan.";
    }
}

$customers = $conn->query("SELECT customer_id, name FROM customer");
$staffs    = $conn->query("SELECT staff_id, name FROM staff WHERE is_active = 1");
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siti Dobi - Tambah Transaksi</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body { padding: 40px; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .form-container { max-width: 500px; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); width: 100%; }
        h2 { color: #0f172a; margin-bottom: 20px; font-size: 1.4rem; font-weight: 600; text-align: center; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #64748b; font-size: 0.9rem; }
        select, input { width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 1rem; box-sizing: border-box; }
        .btn-submit { background-color: #0284c7; color: white; border: none; padding: 12px; width: 100%; border-radius: 8px; cursor: pointer; font-weight: 500; font-size: 1rem; margin-top: 10px; }
        .btn-submit:hover { background-color: #0369a1; }
        .btn-back { display: block; text-align: center; margin-top: 15px; color: #64748b; text-decoration: none; font-size: 0.9rem; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Daftar Transaksi Dobi Baru</h2>
    <form action="tambah_transaksi.php" method="POST">
        
        <div class="form-group">
            <label>Pilih Pelanggan</label>
            <select name="customer_id" required>
                <?php while($c = $customers->fetch_assoc()) {
                    echo "<option value='".$c['customer_id']."'>".htmlspecialchars($c['name'])."</option>";
                } ?>
            </select>
        </div>

        <div class="form-group">
            <label>Staf Yang Bertugas</label>
            <select name="staff_id" required>
                <?php while($s = $staffs->fetch_assoc()) {
                    $selected = ($s['staff_id'] == $_SESSION['staff']['staff_id']) ? ' selected' : '';
                    echo "<option value='".$s['staff_id']."'".$selected.">".htmlspecialchars($s['name'])."</option>";
                } ?>
            </select>
        </div>

        <div class="form-group">
            <label>Jumlah Bayaran (RM)</label>
            <input type="number" step="0.01" name="total_amount" placeholder="Contoh: 15.50" required>
        </div>

        <div class="form-group">
            <label>Kaedah Pembayaran</label>
            <select name="payment_method">
                <option value="cash">Cash</option>
                <option value="ewallet">E-Wallet</option>
                <option value="online_transfer">Online Transfer</option>
                <option value="debit_card">Kad Debit</option>
            </select>
        </div>

        <div class="form-group">
            <label>Status Pesanan</label>
            <select name="status">
                <option value="pending">Pending (Dalam Basuhan)</option>
                <option value="completed">Completed (Selesai)</option>
            </select>
        </div>

        <button type="submit" name="simpan_transaksi" class="btn-submit">Simpan Transaksi</button>
        <a href="index.php" class="btn-back">Batal & Kembali</a>
    </form>
</div>

</body>
</html>