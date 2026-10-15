<?php
session_start();
// SEKATAN KESELAMATAN: Jika tiada rekod session staf, automatik tendang balik ke staff_login.php!
if (!isset($_SESSION['staff'])) {
    header("Location: staff_login.php");
    exit();
}

// 1. MEMBINA SAMBUNGAN DATABASE
$conn = new mysqli("localhost", "root", "", "siti_dobi_db");
if ($conn->connect_error) {
    die("Sambungan gagal: " . $conn->connect_error);
}

// Ambil maklumat peranan user sedia ada
$current_user_role = $_SESSION['staff']['role'];

// ==========================================
// LOGIK GANTUNG / AKTIFKAN STAF (Hanya untuk Admin/Super Admin)
// Guna soft delete (is_active = 0) sebab staf yang ada transaksi tak boleh di-DELETE (foreign key).
// Rekod transaksi lama kekal utuh, dan staf yang digantung tak boleh log masuk.
// ==========================================
if (isset($_GET['toggle_staff_id']) && $current_user_role === 'admin') {
    $toggle_id = intval($_GET['toggle_staff_id']);

    // Elakkan daripada tergantung akaun sendiri secara tidak sengaja
    if ($toggle_id == $_SESSION['staff']['staff_id']) {
        echo "<script>alert('Ralat: Anda tidak boleh menggantung akaun anda sendiri yang sedang aktif!'); window.location='index.php?page=staff';</script>";
    } else {
        $stmt = $conn->prepare("UPDATE staff SET is_active = 1 - is_active WHERE staff_id = ?");
        $stmt->bind_param("i", $toggle_id);
        $stmt->execute();
        if ($stmt->affected_rows === 1) {
            echo "<script>alert('Status staf berjaya dikemas kini!'); window.location='index.php?page=staff';</script>";
        } else {
            echo "<script>alert('Ralat: Staf tidak dijumpai.'); window.location='index.php?page=staff';</script>";
        }
    }
    exit();
}

// 2. QUERY LIVE UNTUK KAD STATISTIK
$query_sales = $conn->query("SELECT SUM(total_amount) AS total_sales FROM transaction WHERE status = 'completed'");
$row_sales = $query_sales->fetch_assoc();
$live_sales = $row_sales['total_sales'] ?? 0.00;

$query_orders = $conn->query("SELECT COUNT(*) AS total_orders FROM transaction WHERE status = 'completed'");
$row_orders = $query_orders->fetch_assoc();
$live_orders = $row_orders['total_orders'] ?? 0;

$query_cust = $conn->query("SELECT COUNT(*) AS total_customers FROM customer");
$row_cust = $query_cust->fetch_assoc();
$live_customers = $row_cust['total_customers'] ?? 0;

// Tentukan halaman mana nak papar (dashboard transaksi atau urus staf)
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siti Dobi - Dashboard Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-image: linear-gradient(rgba(15, 23, 42, 0.45), rgba(15, 23, 42, 0.45)), url('bgdobi.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
        }
        .main-header h1 { color: #ffffff; }
        .logo h2 {
            color: #ffffff !important;
            font-size: 1.3rem;
            line-height: 1.4;
            margin: 0;
            text-align: center;
        }
        .btn-print-container { margin-bottom: 15px; text-align: right; }
        .btn-print {
            background-color: #ea580c; color: white; border: none; padding: 10px 20px; border-radius: 6px; 
            font-family: 'Poppins', sans-serif; font-weight: 500; font-size: 0.9rem; cursor: pointer;
            display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn-print:hover { background-color: #c2410c; }
        
        /* Gaya navigasi sidebar */
        .nav-menu a {
            display: block; padding: 12px 20px; color: #cbd5e1; text-decoration: none; font-weight: 500; border-radius: 8px; margin-bottom: 5px;
        }
        .nav-menu a.active, .nav-menu a:hover {
            background: #0284c7; color: white;
        }
        
        /* Butang aksi delete */
        .btn-delete {
            background-color: #ef4444; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.85rem; font-weight: 500;
        }
        .btn-delete:hover { background-color: #b91c1c; }
        
        .btn-add {
            background-color: #22c55e; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 0.9rem; font-weight: 600; display: inline-block; margin-bottom: 15px;
        }
        .btn-add:hover { background-color: #16a34a; }

        @media print {
            .sidebar, .logout-btn, .sidebar-footer, .btn-print-container, .btn-add, .btn-delete, th:last-child, td:last-child { 
                display: none !important; 
            }
            body { background: white !important; color: black !important; padding: 0 !important; margin: 0 !important; }
            body::before { display: none !important; }
            .main-content { margin-left: 0 !important; padding: 0 !important; width: 100% !important; max-width: 100% !important; }
            .main-header h1 { color: #0f172a !important; }
            .card, table, th, td { border: 1px solid #cbd5e1 !important; color: #0f172a !important; box-shadow: none !important; }
        }
    </style>
</head>
<body>

    <div class="dashboard-container">
        
        <aside class="sidebar">
            <div class="logo">
               <h2>Hello, <?php echo htmlspecialchars($_SESSION['staff']['name']); ?></h2>
               <span style="color:#64748b; font-size:0.8rem; text-transform:uppercase;">[<?php echo $current_user_role; ?>]</span>
            </div>
            <nav class="nav-menu" style="margin-top: 20px;">
                <a href="index.php?page=dashboard" class="<?php echo $page === 'dashboard' ? 'active' : ''; ?>">📊 Dashboard</a>
                
                <!-- KOD BARU: Menu Urus Staf Hanya Muncul Jika User Ialah 'admin' -->
                <?php if ($current_user_role === 'admin'): ?>
                    <a href="index.php?page=staff" class="<?php echo $page === 'staff' ? 'active' : ''; ?>">👥 Pengurusan Staf</a>
                <?php endif; ?>
            </nav>
            <div class="sidebar-footer">
                <p style="font-size: 0.8rem; color:#94a3b8; margin-bottom:5px;">Staf: <?php echo htmlspecialchars($_SESSION['staff']['name']); ?></p>
                <a href="logout.php" class="logout-btn">Log Keluar</a>
            </div>
        </aside>

        <main class="main-content">
            
            <header class="main-header">
                <h1>Welcome To Siti Dobi</h1>
                <div class="date-badge">Mei 2026</div>
            </header>

            <section class="stats-grid">
                <div class="card card-blue">
                    <h3>RM <?php echo number_format($live_sales, 2); ?></h3>
                    <p>Pendapatan Bulan Ini</p>
                </div>
                <div class="card card-green">
                    <h3><?php echo $live_orders; ?> Orders</h3>
                    <p>Transaksi Selesai</p>
                </div>
                <div class="card card-orange">
                    <h3><?php echo $live_customers; ?> Pelanggan</h3>
                    <p>Jumlah Pelanggan Berdaftar</p>
                </div>
            </section>

            <!-- ======================================================================= -->
            <!-- HALAMAN UTAMA A: JADUAL TRANSAKSI (DEFAULT) -->
            <!-- ======================================================================= -->
            <?php if ($page === 'dashboard'): ?>
            <section class="table-section">
                <div class="btn-print-container">
                    <button onclick="window.print()" class="btn-print">🖨️ Cetak Laporan Transaksi (PDF)</button>
                </div>

                <div class="table-header">
                    <h2>Transaksi Terkini (Live)</h2>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Pelanggan</th>
                                <th>Kaedah Bayaran</th>
                                <th>Jumlah (RM)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql_table = "SELECT t.transaction_id, c.name AS customer_name, t.payment_method, t.total_amount, t.status 
                                          FROM transaction t
                                          JOIN customer c ON t.customer_id = c.customer_id
                                          ORDER BY t.transaction_id DESC";
                            $result_table = $conn->query($sql_table);

                            if ($result_table && $result_table->num_rows > 0) {
                                while($row = $result_table->fetch_assoc()) {
                                    $pay_method = strtolower($row['payment_method']); 
                                    $status_class = strtolower($row['status']); 
                                    echo "<tr>";
                                    echo "<td>#" . $row['transaction_id'] . "</td>";
                                    echo "<td>" . htmlspecialchars($row['customer_name']) . "</td>";
                                    echo "<td><span class='badge " . $pay_method . "'>" . ucwords(str_replace('_', ' ', $pay_method)) . "</span></td>";
                                    echo "<td>" . number_format($row['total_amount'], 2) . "</td>";
                                    echo "<td><span class='status-badge " . $status_class . "'>" . ucfirst($status_class) . "</span></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' style='text-align:center;'>Tiada transaksi direkodkan lagi.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <?php endif; ?>

            <!-- ======================================================================= -->
            <!-- HALAMAN UTAMA B: PENGURUSAN STAF (HANYA UNTUK SUPER ADMIN / ROLE ADMIN) -->
            <!-- ======================================================================= -->
            <?php if ($page === 'staff' && $current_user_role === 'admin'): ?>
            <section class="table-section">
                <div class="table-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h2>Sistem Pengurusan Staf (Super Admin Hub)</h2>
                    <!-- Pautan ke fail register yang kita bina tadi untuk tambah staf baru! -->
                    <a href="staff_register.php" class="btn-add">➕ Tambah Staf Baru</a>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID Staf</th>
                                <th>Nama Penuh</th>
                                <th>Username</th>
                                <th>Peranan (Role)</th>
                                <th>Status Tugas</th>
                                <th style="text-align: center;">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql_staff = "SELECT staff_id, name, username, role, is_active FROM staff ORDER BY staff_id ASC";
                            $result_staff = $conn->query($sql_staff);

                            if ($result_staff && $result_staff->num_rows > 0) {
                                while($s_row = $result_staff->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $s_row['staff_id'] . "</td>";
                                    echo "<td>" . htmlspecialchars($s_row['name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($s_row['username']) . "</td>";
                                    echo "<td><strong style='color: " . ($s_row['role'] === 'admin' ? '#ea580c' : '#0284c7') . ";'>" . strtoupper($s_row['role']) . "</strong></td>";
                                    echo "<td>" . ($s_row['is_active'] == 1 ? '✅ Aktif' : '❌ Digantung') . "</td>";
                                    echo "<td style='text-align: center;'>";
                                    if ($s_row['is_active'] == 1) {
                                        echo "<a href='index.php?page=staff&toggle_staff_id=" . $s_row['staff_id'] . "' class='btn-delete' onclick=\"return confirm('Adakah anda pasti mahu menggantung staf " . htmlspecialchars($s_row['name'], ENT_QUOTES) . "?');\">🚫 Gantung</a>";
                                    } else {
                                        echo "<a href='index.php?page=staff&toggle_staff_id=" . $s_row['staff_id'] . "' class='btn-add' style='margin-bottom:0; padding:6px 12px; font-size:0.85rem;'>✅ Aktifkan</a>";
                                    }
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <?php endif; ?>

        </main>
    </div>

    <?php $conn->close(); ?>
</body>
</html>