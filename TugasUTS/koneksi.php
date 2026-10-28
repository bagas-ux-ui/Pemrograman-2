<?php
// =====================================================
// koneksi.php - Koneksi database + fungsi bantu
// Ubah nilai di bawah sesuai server lokal Anda.
// =====================================================
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';              // XAMPP/Laragon default: kosong
$DB_NAME = 'db_pendaftaran';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mode exception agar error bisa ditangkap dengan try/catch
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    error_log('Koneksi DB gagal: ' . $e->getMessage()); // detail hanya di log server
    http_response_code(500);
    die('<h3 style="font-family:sans-serif">Tidak dapat terhubung ke database.</h3>
         <p style="font-family:sans-serif">Pastikan MySQL aktif, database
         <b>db_pendaftaran</b> sudah diimpor, dan pengaturan di <code>koneksi.php</code> benar.</p>');
}

// ---------- Konstanta pilihan ----------
const DAFTAR_FAKULTAS = ['Teknik', 'Ekonomi', 'Hukum', 'Kedokteran', 'Ilmu Komputer', 'Keguruan dan Ilmu Pendidikan'];
const DAFTAR_TEMPAT_TES = ['Gedung A', 'Gedung B', 'Viktor'];

// ---------- Aturan kode pendaftaran ----------
// Format: [Tempat][Gelombang]-[No Urut]-[Bulan]   contoh: A2-101-9
//  - Karakter 1 : A = Gedung A, B = Gedung B, V = Viktor
//  - Karakter 2 : gelombang tes (angka 1-9)
//  - Bagian tengah : nomor urut peserta
//  - Karakter terakhir : bulan tes (1-9, A=Oktober, B=November, C=Desember)
// Jika aturan di soal berbeda, cukup ubah fungsi parse_kode() ini.
function parse_kode(string $kode): ?array
{
    $kode = strtoupper(trim($kode));
    if (!preg_match('/^([ABV])([1-9])-(\d{1,6})-([1-9ABC])$/', $kode, $m)) {
        return null;
    }
    $tempat = ['A' => 'Gedung A', 'B' => 'Gedung B', 'V' => 'Viktor'];
    $bulan = [
        '1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April',
        '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus',
        '9' => 'September', 'A' => 'Oktober', 'B' => 'November', 'C' => 'Desember',
    ];
    return [
        'kode'     => $kode,
        'tempat'   => $tempat[$m[1]],
        'gelombang' => (int)$m[2],
        'no_urut'  => $m[3],
        'bulan'    => $bulan[$m[4]],
    ];
}

// ---------- Perhitungan nilai ----------
// Rata-rata = (Matematika + Inggris + Umum) / 3
function hitung_rata(float $mat, float $ing, float $umum): float
{
    return ($mat + $ing + $umum) / 3;
}

// >= 70 Lulus | 60 s/d < 70 Cadangan | < 60 Tidak Lulus
function tentukan_status(float $rata): string
{
    if ($rata >= 70) return 'Lulus';
    if ($rata >= 60) return 'Cadangan';
    return 'Tidak Lulus';
}

// ---------- Helper umum ----------
function e($s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function set_flash(string $type, string $msg, array $errors = []): void
{
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg, 'errors' => $errors];
}