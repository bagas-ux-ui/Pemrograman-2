<?php
/*
 * Judul Materi : Latihan 5 - Menampilkan Data
 * Nama         : Akbar Aditiya Sugianto
 * NIM          : 231011403729
 * Kelas        : 07 TPLM 004
 * Universitas  : Universitas Pamulang
 */

mysql_connect('localhost', 'root', '');
// mysql_select_db('lat_dbase');
$hasil = mysql_db_query('lat_dbase', 'select * from tbl_mhs');
while ($data = mysql_fetch_row($hasil)) {
    echo "$data[0] $data[1] $data[2]<br>";
}
?>
