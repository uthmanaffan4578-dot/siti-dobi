<?php
session_start();
if (!isset($_SESSION['customer'])) {
    header("Location: customer_login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "siti_dobi_db");
if ($conn->connect_error) { die("Sambungan gagal: " . $conn->connect_error); }

$cust_id = intval($_SESSION['customer']['customer_id']);

// Transaction IDs from the payment that was just made
$txn_ids = isset($_SESSION['last_transaction_ids']) ? $_SESSION['last_transaction_ids'] : [];

if (empty($txn_ids)) {
    // Nothing in the session: fall back to this customer's latest transaction (never another customer's)
    $stmt = $conn->prepare("SELECT MAX(transaction_id) AS max_id FROM transaction WHERE customer_id = ?");
    $stmt->bind_param("i", $cust_id);
    $stmt->execute();
    $latest = $stmt->get_result()->fetch_assoc();
    if ($latest['max_id']) {
        $txn_ids = [$latest['max_id']];
    }
}

if (empty($txn_ids)) {
    echo "<div style='padding:20px; font-family:sans-serif; text-align:center;'>
            <h3>Tiada rekod transaksi dijumpai lagi.</h3>
            <a href='customer_dashboard.php' style='color:#000; font-weight:bold;'>Kembali ke Dashboard</a>
          </div>";
    exit();
}

// IDs are cast to int, so they are safe to build into the IN (...) list
$ids_string = implode(",", array_map('intval', $txn_ids));

// The customer_id filter ensures customers can only see their own transactions
$sql = "SELECT t.*, c.name AS customer_name FROM transaction t 
        JOIN customer c ON t.customer_id = c.customer_id 
        WHERE t.transaction_id IN ($ids_string) AND t.customer_id = $cust_id
        ORDER BY t.transaction_id ASC";
$result = $conn->query($sql);

$items = [];
$total_all = 0;
$customer_name = "";
$first_txn_id = 0;

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if (empty($customer_name)) {
            $customer_name = $row['customer_name'];
            $first_txn_id = $row['transaction_id']; // The first transaction ID is used as the invoice number
        }
        $items[] = $row;
        $total_all += $row['total_amount'];
    }
} else {
    die("Invois tidak ditemui.");
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>INVOICE #<?php echo $first_txn_id; ?></title>
   <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 40px;
            background-color: #ffffff;
            color: #000000;
            position: relative; /* Positioning context for the watermark */
        }
        
        /* Faded logo watermark behind the invoice */
        .invoice-container::before {
            content: "";
            position: absolute;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 350px;
            height: 200px;
            background-image: url('siti dobi.png');
            background-size: contain;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0.25; /* Faint enough to keep the text readable when printed */
            z-index: -1; /* Behind the text */
            pointer-events: none;
        }

        .invoice-container {
            max-width: 700px;
            margin: 0 auto;
            position: relative; /* Anchors the ::before watermark */
        }
        .title {
            font-size: 42px;
            font-weight: bold;
            letter-spacing: 1px;
            margin: 0 0 30px 0;
        }
        hr {
            border: none;
            border-top: 2px solid #000000;
            margin-bottom: 30px;
        }
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            font-size: 14px;
            line-height: 1.6;
        }
        .info-block h4 {
            margin: 0 0 5px 0;
            text-transform: uppercase;
            color: #555555;
            font-size: 12px;
        }
        .info-block p {
            margin: 0;
            font-weight: bold;
            font-size: 16px;
        }
        .inv-number {
            font-size: 20px;
            font-weight: bold;
        }
        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .item-table th {
            border-bottom: 2px solid #000000;
            padding: 10px 0;
            text-align: left;
            font-size: 14px;
        }
        .item-table td {
            border-bottom: 1px dashed #cccccc;
            padding: 15px 0;
            font-size: 15px;
        }
        .total-row {
            background-color: #000000;
            color: #ffffff;
            padding: 12px;
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 18px;
            margin-top: 20px;
        }
        .btn-group {
            margin-top: 40px;
            text-align: center;
        }
        .btn {
            background: #ffffff;
            color: #000000;
            border: 2px solid #000000;
            padding: 10px 20px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            margin: 0 5px;
            display: inline-block;
        }
        .btn:hover {
            background: #000000;
            color: #ffffff;
        }
        @media print {
            .btn-group { display: none !important; }
            body { padding: 0; }
            /* Browsers skip background images when printing unless forced */
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</head>
<body>

<div class="invoice-container">
    <div class="title">INVOICE</div>
    <hr>

    <div class="info-section">
        <div class="info-block">
            <h4>From</h4>
            <p>Siti Dobi Company</p>
        </div>
        <div class="info-block" style="text-align: right;">
            <span class="inv-number">INV-00<?php echo $first_txn_id; ?></span>
        </div>
    </div>

    <div class="info-section">
        <div class="info-block">
            <h4>Bill To</h4>
            <p><?php echo htmlspecialchars($customer_name); ?></p>
        </div>
    </div>

    <table class="item-table">
        <thead>
            <tr>
                <th style="width: 50%;">Item</th>
                <th style="text-align: center;">Qty</th>
                <th style="text-align: right;">Rate</th>
                <th style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['notes']); ?></td>
                <td style="text-align: center;">1</td>
                <td style="text-align: right;">RM <?php echo number_format($item['total_amount'], 2); ?></td>
                <td style="text-align: right;">RM <?php echo number_format($item['total_amount'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="total-row">
        <span>TOTAL</span>
        <span>RM <?php echo number_format($total_all, 2); ?></span>
    </div>

    <div class="btn-group">
        <button onclick="window.print()" class="btn">🖨️ Print Invoice</button>
        <a href="customer_dashboard.php" class="btn">⬅️ Back to Dashboard</a>
    </div>
</div>

</body>
</html>