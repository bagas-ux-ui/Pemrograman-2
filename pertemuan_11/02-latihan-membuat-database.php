<?php
/*
 * Judul Materi : Latihan 2 - Membuat Database
 * Nama         : Akbar Aditiya Sugianto
 * NIM          : 231011403729
 * Kelas        : 07 TPLM 004
 * Universitas  : Universitas Pamulang
 */

mysql_connect('localhost', 'root', '');
$dbname = 'lat_dbase';
$cek = mysql_query("CREATE DATABASE $dbname")
    or die("Couldn't Create Database: $dbname");
if ($cek) {
    echo "Database $dbname berhasil dibuat";
}
?>
