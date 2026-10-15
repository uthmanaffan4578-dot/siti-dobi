<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang ke Siti Dobi</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Poppins', sans-serif; 
            /* Dark blue overlay on top of the background photo */
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
        .welcome-box { 
            background: rgba(15, 23, 42, 0.7); 
            backdrop-filter: blur(12px);
            border: 1px solid #1e293b;
            padding: 45px 40px; 
            border-radius: 20px; 
            box-shadow: 0 20px 45px rgba(0,0,0,0.4); 
            width: 100%; 
            max-width: 450px; 
            text-align: center;
        }
        h1 { 
            background: linear-gradient(to right, #ffffff, #38bdf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 2.2rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
        p.tagline {
            color: #94a3b8;
            font-size: 0.95rem;
            margin-bottom: 40px;
        }
        .portal-options {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .portal-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            padding: 16px;
            border-radius: 12px;
            font-size: 1.05rem;
            font-weight: 500;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .portal-btn:hover {
            transform: translateY(-2px);
        }
        /* Customer button */
        .btn-customer {
            background: linear-gradient(135deg, #0284c7, #0369a1);
            color: white;
            box-shadow: 0 4px 20px rgba(2, 132, 199, 0.25);
        }
        .btn-customer:hover {
            box-shadow: 0 6px 25px rgba(2, 132, 199, 0.4);
        }
        /* Staff button */
        .btn-staff {
            background: #1e293b;
            color: #38bdf8;
            border: 1px solid #334155;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }
        .btn-staff:hover {
            background: #273549;
            border-color: #38bdf8;
        }
        .footer-text {
            margin-top: 35px;
            color: #64748b;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>

<div class="welcome-box">
    <h1>Siti Dobi</h1>
    <p class="tagline">Sistem Aplikasi Layan Diri Pintar</p>
    
    <div class="portal-options">
        <!-- Customer portal -->
        <a href="customer_login.php" class="portal-btn btn-customer">
            👥 Masuk Portal Pelanggan
        </a>
        
        <!-- Staff / admin portal -->
        <a href="staff_login.php" class="portal-btn btn-staff">
            🛠️ Pengurusan Staf & Admin
        </a>
    </div>

    <p class="footer-text">© 2026 Siti Dobi Real-Time System. All Rights Reserved.</p>
</div>

</body>
</html>