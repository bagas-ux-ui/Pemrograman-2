<?php
/*
 * Judul Materi : Latihan - Menyimpan Data dari Form
 * Nama         : Akbar Aditiya Sugianto
 * NIM          : 231011403729
 * Kelas        : 07 TPLM 004
 * Universitas  : Universitas Pamulang
 */

$con = mysql_connect('localhost', 'root', '');
if (!$con) {
    die('Could not connect: ' . mysql_error());
}
mysql_select_db('lat_dbase', $con);

$sql = "INSERT INTO tbl_mhs (FirstName, LastName, Age)
VALUES
('$_POST[firstname]','$_POST[lastname]','$_POST[age]')";

if (!mysql_query($sql, $con)) {
    die('Error: ' . mysql_error());
}
echo '1 record added';

mysql_close($con);
?>
