<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siti Dobi - About Us</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 40px 20px;
            min-height: 100vh;
            box-sizing: border-box;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        /* Full-page background image, fixed behind the content */
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: linear-gradient(rgba(15, 23, 42, 0.65), rgba(15, 23, 42, 0.65)), url('bg-about.webp');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            z-index: -1;
        }

        /* Main content card */
        .content-container {
            background: rgba(255, 255, 255, 0.96); 
            max-width: 850px;
            width: 100%;
            padding: 45px;
            border-radius: 16px;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.4);
            box-sizing: border-box;
        }

        /* Page title in the brand orange */
        .main-heading {
            color: #ce0000;
            font-size: 2.6rem;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 25px;
        }

        /* Section headings for Vision and Mission */
        .sub-heading {
            color: #ce0000;
            font-size: 1.6rem;
            font-weight: 600;
            margin-top: 30px;
            margin-bottom: 12px;
        }

        p {
            color: #ce0000; 
            font-size: 1rem;
            line-height: 1.6;
            margin-top: 0;
            margin-bottom: 18px;
            text-align: justify;
        }

        /* Back button */
        .btn-back {
            display: inline-block;
            background-color: #a91e1e;
            color: white;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.95rem;
            margin-top: 20px;
            transition: background 0.2s, transform 0.2s;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-back:hover {
            background-color: #334155;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<div class="content-container">
    <!-- Title -->
    <h1 class="main-heading">We Are Everyone's Laundry!</h1>
    
    <p>When you mentioned <strong>Siti Dobi®</strong>, you are going to your own place.</p>
    
    <p>We provide our customers not just solutions for their laundry needs, but also a pleasant, comfortable and clean environment that instills confidence in their laundry experience.</p>
    
    <p>The corporate colours of dark grey to black and complemented by dark yellow, convey a sense of reliability and familiarity.</p>
    
    <p>To our investors, <strong>Siti Dobi®</strong> offers a reliable and profitable business solution that can provide passive income for families.</p>
    
    <p>The <strong>Siti Dobi®</strong> business model has also proven to be resilient, continuing to expand even during the pandemic.</p>

    <!-- Vision -->
    <h2 class="sub-heading">Vision</h2>
    <p>Simplicity, Focused, to build a century-old brand.</p>

    <!-- Mission -->
    <h2 class="sub-heading">Mission</h2>
    <p>Empowering a global brand, Driving prosperity to our Partners.</p>

    <!-- Back to the welcome page -->
    <a href="welcome.php" class="btn-back">⬅️ Kembali ke Menu Utama</a>
</div>

</body>
</html>