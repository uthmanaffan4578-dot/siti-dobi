<?php
session_start();
// Padamkan semua data session yang sedang aktif
session_unset();
session_destroy();

// Hantar pengguna kembali ke halaman pilihan portal utama
header("Location: welcome.php");
exit();
?>