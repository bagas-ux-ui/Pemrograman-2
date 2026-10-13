<!DOCTYPE html>
<html>
<head>
    <title>Contoh Penggunaan UDF</title>
</head>
<body>

<form method="POST" action="">
    Masukkan Bilangan Pertama:<br>
    <input type="number" name="A" required>
    <br>

    Masukkan Bilangan Kedua:<br>
    <input type="number" name="B" required>
    <br>

    <input type="submit" name="hitung" value="Hitung">
</form>

<?php

// Fungsi penjumlahan
function jumlah($A, $B) {
    return $A + $B;
}

// Fungsi pengurangan
function kurang($A, $B) {
    return $A - $B;
}

// Fungsi perkalian
function kali($A, $B) {
    return $A * $B;
}

// Fungsi pembagian
function bagi($A, $B) {
    return $A / $B;
}

// Memproses data jika tombol Hitung ditekan
if (isset($_POST['hitung'])) {

    $A = (float) $_POST['A'];
    $B = (float) $_POST['B'];

    echo "<br>";
    echo "Bilangan Pertama: " . $A . "<br>";
    echo "Bilangan Kedua: " . $B . "<br><br>";

    echo "Hasil Penjumlahan 2 buah bilangan<br>";
    $jumlahbil = jumlah($A, $B);
    printf("Penjumlahan: %g + %g = %g<br><br>",
        $A, $B, $jumlahbil);

    echo "Hasil Pengurangan 2 buah bilangan<br>";
    $kurangbil = kurang($A, $B);
    printf("Pengurangan: %g - %g = %g<br><br>",
        $A, $B, $kurangbil);

    echo "Hasil Perkalian 2 buah bilangan<br>";
    $kalibil = kali($A, $B);
    printf("Perkalian: %g * %g = %g<br><br>",
        $A, $B, $kalibil);

    echo "Hasil Pembagian 2 buah bilangan<br>";

    if ($B != 0) {
        $bagibil = bagi($A, $B);
        printf("Pembagian: %g / %g = %g<br><br>",
            $A, $B, $bagibil);
    } else {
        echo "Pembagian tidak dapat dilakukan karena bilangan kedua adalah 0.<br>";
    }
}

?>

</body>
</html>