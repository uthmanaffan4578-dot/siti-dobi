<?php
session_start();
if (!isset($_SESSION['customer'])) {
    header("Location: customer_login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "siti_dobi_db");
$cust_id = intval($_SESSION['customer']['customer_id']);

// Prices are defined server-side; never trust amounts sent by the form
$service_prices = ['basuh' => 5.00, 'kering' => 4.00];

// 1. Simulated payment: one transaction per selected service
if (isset($_POST['bayar_dobi'])) {
    // Keep only known service names from the submitted checkboxes
    $services_selected = isset($_POST['services']) && is_array($_POST['services']) ? $_POST['services'] : [];
    $services_selected = array_values(array_intersect(array_unique($services_selected), array_keys($service_prices)));

    if (empty($services_selected)) {
        echo "<script>alert('Sila pilih sekurang-kurangnya satu jenis perkhidmatan!'); window.location='customer_dashboard.php';</script>";
        exit();
    }

    // Only 'ewallet' and 'debit_card' are offered; anything else falls back to e-wallet
    $payment_method = (isset($_POST['payment_method']) && $_POST['payment_method'] === 'debit_card') ? 'debit_card' : 'ewallet';

    $total_amount = 0;

    // One transaction per service, so the stamp trigger awards one stamp per service
    $success = true;
    $inserted_ids = []; // Passed to the invoice page

    $stmt_insert = $conn->prepare("INSERT INTO transaction (customer_id, staff_id, total_amount, payment_method, status, notes)
                                   VALUES (?, 1, ?, ?, 'pending', ?)");
    $stmt_update = $conn->prepare("UPDATE transaction SET status = 'completed' WHERE transaction_id = ?");

    foreach ($services_selected as $current_service) {
        $price = $service_prices[$current_service];
        $note = "Service: " . ucfirst($current_service);

        // Insert as 'pending' first...
        $stmt_insert->bind_param("idss", $cust_id, $price, $payment_method, $note);

        if ($stmt_insert->execute()) {
            $new_txn_id = $conn->insert_id;
            $inserted_ids[] = $new_txn_id;
            $total_amount += $price;

            // ...then mark it 'completed': the stamp trigger only fires on UPDATE
            $stmt_update->bind_param("i", $new_txn_id);
            $stmt_update->execute();
        } else {
            $success = false;
        }
    }

    if (!$success) {
        echo "<script>alert('Ralat: Bayaran gagal diproses. Sila cuba lagi.'); window.location='customer_dashboard.php';</script>";
        exit();
    }

    if ($success) {
        // invoice.php reads these IDs to show this payment
        $_SESSION['last_transaction_ids'] = $inserted_ids;
        
        echo "<script>
            alert('Bayaran Berjaya! RM " . number_format($total_amount, 2) . " telah diproses. Sila semak invois anda.');
            window.location.href = 'invoice.php';
        </script>";
        exit();
    }
}

// 2. Redeem a free wash
// The stamp trigger creates a 'pending' reward_redemption row on every 10th stamp.
// Redeeming marks the oldest pending row as 'redeemed' and decrements the balance.
// Both steps run in one DB transaction, so a failure can't lose the customer's reward.
if (isset($_POST['redeem_reward'])) {
    $redeemed = false;
    $conn->begin_transaction();

    $stmt = $conn->prepare("UPDATE loyalty_card SET total_free_washes = total_free_washes - 1
                            WHERE customer_id = ? AND total_free_washes > 0");
    $stmt->bind_param("i", $cust_id);
    $stmt->execute();

    if ($stmt->affected_rows === 1) {
        $stmt = $conn->prepare("SELECT r.redemption_id FROM reward_redemption r
                                JOIN loyalty_card lc ON r.card_id = lc.card_id
                                WHERE lc.customer_id = ? AND r.status = 'pending'
                                ORDER BY r.redemption_id ASC LIMIT 1 FOR UPDATE");
        $stmt->bind_param("i", $cust_id);
        $stmt->execute();
        $pending = $stmt->get_result()->fetch_assoc();

        if ($pending) {
            $stmt = $conn->prepare("UPDATE reward_redemption SET status = 'redeemed', redeemed_at = NOW() WHERE redemption_id = ?");
            $stmt->bind_param("i", $pending['redemption_id']);
            $stmt->execute();
            $redeemed = ($stmt->affected_rows === 1);
        }
    }

    if ($redeemed) {
        $conn->commit();
        echo "<script>alert('Tebusan Berjaya! 1 slot Basuh Free telah digunakan.'); window.location='customer_dashboard.php';</script>";
        exit();
    } else {
        $conn->rollback();
        echo "<script>alert('Anda tiada ganjaran Free Wash yang sedia untuk ditebus!');</script>";
    }
}

// 3. Load current customer and loyalty card data
$stmt = $conn->prepare("SELECT * FROM customer WHERE customer_id = ?");
$stmt->bind_param("i", $cust_id);
$stmt->execute();
$cust_data = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("SELECT * FROM loyalty_card WHERE customer_id = ?");
$stmt->bind_param("i", $cust_id);
$stmt->execute();
$card_data = $stmt->get_result()->fetch_assoc();

$current_stamps = $card_data['stamp_count'] ?? 0;
$free_washes    = $card_data['total_free_washes'] ?? 0;
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Siti Dobi - Dashboard Pelanggan</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Poppins', sans-serif; 
            min-height: 100vh;
            padding: 30px; 
            margin: 0; 
            box-sizing: border-box;
            position: relative;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: linear-gradient(rgba(15, 23, 42, 0.45), rgba(15, 23, 42, 0.45)), url('bgdobi.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            filter: blur(5px);
            transform: scale(1.05);
            z-index: -1;
        }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px; }
        h1 { font-size: 1.4rem; color: #0f172a; margin: 0; }
        .stats-display { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px; }
        .point-card { background: linear-gradient(135deg, #0284c7, #0369a1); color: white; padding: 20px; border-radius: 12px; text-align: center; }
        .point-card h2 { font-size: 2.2rem; margin: 0; }
        .point-card p { margin: 5px 0 0 0; font-size: 0.9rem; opacity: 0.9; }
        .reward-card { background: linear-gradient(135deg, #22c55e, #15803d); color: white; padding: 20px; border-radius: 12px; text-align: center; }
        .reward-card h2 { font-size: 2.2rem; margin: 0; }
        .reward-card p { margin: 5px 0 0 0; font-size: 0.9rem; opacity: 0.9; }
        .section-box { border: 1px solid #e2e8f0; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .section-box h3 { margin-top: 0; color: #1e293b; margin-bottom: 10px; }
        .service-option { display: flex; justify-content: space-between; align-items: center; padding: 12px; border: 1px solid #f1f5f9; border-radius: 8px; margin-bottom: 10px; background-color: #f8fafc; cursor: pointer; }
        .service-option input { width: 18px; height: 18px; cursor: pointer; }
        .service-option:hover { background-color: #f1f5f9; }
        .btn-blue { background-color: #0284c7; color: white; border: none; padding: 12px; border-radius: 8px; cursor: pointer; width: 100%; font-size: 1rem; font-weight: 500; margin-top: 5px; }
        .btn-orange { background-color: #f97316; color: white; border: none; padding: 12px; border-radius: 8px; cursor: pointer; width: 100%; font-size: 1rem; font-weight: 500; }
        .btn-orange:disabled { background-color: #cbd5e1; cursor: not-allowed; }
        .logout { color: #ef4444; text-decoration: none; font-size: 0.9rem; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Hai, <?php echo htmlspecialchars($cust_data['name']); ?> 👋</h1>
        <a href="logout.php" class="logout">Log Keluar</a>
    </div>

    <div class="stats-display">
        <div class="point-card">
            <h2><?php echo $current_stamps; ?> / 10</h2>
            <p>Stamp Dikumpul</p>
        </div>
        <div class="reward-card">
            <h2><?php echo $free_washes; ?></h2>
            <p>Baki Basuh Percuma</p>
        </div>
    </div>

    <div class="section-box" style="background-color: #f0fdf4; border-color: #bbf7d0;">
        <h3>🎁 Penebusan Ganjaran</h3>
        <p style="font-size: 0.9rem; color: #166534; margin-bottom: 15px;">Bila stamp cukup 10, tiket Basuh Percuma akan bertambah.</p>
        <form action="customer_dashboard.php" method="POST">
            <button type="submit" name="redeem_reward" class="btn-orange" <?php echo ($free_washes <= 0) ? 'disabled' : ''; ?>>
                <?php echo ($free_washes > 0) ? 'Tebus 1 Slot Basuh Free Sekarang' : 'Tiada Tiket Free Wash Sedia Ada'; ?>
            </button>
        </form>
    </div>

    <div class="section-box">
        <h3>🧺 Pilih Servis Layan Diri (Simulator)</h3>
        <form action="customer_dashboard.php" method="POST">
            
            <label class="service-option">
                <div>
                    <strong style="display:block; color:#334155;">🧺 Perkhidmatan Basuh Pakaian</strong>
                    <span style="font-size:0.85rem; color:#64748b;">Mesin Basuh Dobi (+1 Stamp)</span>
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="font-weight:600; color:#0284c7;">RM 5.00</span>
                    <input type="checkbox" name="services[]" value="basuh">
                </div>
            </label>

            <label class="service-option">
                <div>
                    <strong style="display:block; color:#334155;">🔥 Perkhidmatan Keringkan Pakaian</strong>
                    <span style="font-size:0.85rem; color:#64748b;">Mesin Pengering Dobi (+1 Stamp)</span>
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="font-weight:600; color:#0284c7;">RM 4.00</span>
                    <input type="checkbox" name="services[]" value="kering">
                </div>
            </label>

            <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;">

            <h3 style="margin-bottom: 12px; font-size: 1.1rem;">💳 Pilih Kaedah Pembayaran</h3>
            
            <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 10px; background: #f8fafc; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; cursor: pointer;">
                    <input type="radio" name="payment_method" value="ewallet" checked style="width: 18px; height: 18px;">
                    <span style="font-size: 0.95rem; color: #334155; font-weight: 500;"> Touch 'n Go eWallet / DuitNow QR</span>
                </label>
                
                <label style="display: flex; align-items: center; gap: 10px; background: #f8fafc; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; cursor: pointer;">
                    <input type="radio" name="payment_method" value="debit_card" style="width: 18px; height: 18px;">
                    <span style="font-size: 0.95rem; color: #334155; font-weight: 500;"> Kad Debit / Wave (Visa/Mastercard)</span>
                </label>
            </div>

            <button type="submit" name="bayar_dobi" class="btn-blue">Sahkan & Bayar Semasa</button>
        </form>
    </div>
</div>

</body>
</html>