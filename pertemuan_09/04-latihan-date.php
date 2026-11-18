<?php
/*
 * Judul Materi : Latihan 4 - Fungsi Tanggal
 * Nama         : Akbar Aditiya Sugianto
 * NIM          : 231011403729
 * Kelas        : 07 TPLM 004
 * Universitas  : Universitas Pamulang
 */

date_default_timezone_set('Asia/Jakarta');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Tanggal</title>
</head>

<body>
    <div style="font-size: 10px;">
        <?php
        echo 'Sekarang tanggal ';
        echo date('d-F-Y');
        echo '<br>dan jam ';
        echo date('h:i:s A');
        ?>
    </div>
</body>

</html>
