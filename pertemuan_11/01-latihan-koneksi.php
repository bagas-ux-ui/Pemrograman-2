<?php
/*
 * Judul Materi : Latihan 1 - Koneksi MySQL
 * Nama         : Akbar Aditiya Sugianto
 * NIM          : 231011403729
 * Kelas        : 07 TPLM 004
 * Universitas  : Universitas Pamulang
 */

// hostname or ip of server
$servername = 'localhost';

// username and password
$dbusername = 'root';
$dbpassword = '';
$link = mysql_connect("$servername", "$dbusername", "$dbpassword")
    or die('Not able to connect to server');
if ($link) {
    echo 'ok....koneksi berhasil';
}
?>
