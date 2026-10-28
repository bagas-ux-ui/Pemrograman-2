<?php
// =====================================================
// proses.php - Tambah, Edit, Hapus (hanya lewat POST)
// =====================================================
require 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Verifikasi token CSRF
if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    set_flash('error', 'Permintaan tidak valid. Silakan coba lagi.');
    header('Location: index.php');
    exit;
}

$aksi = $_POST['aksi'] ?? '';

// ---------- Ambil & validasi input form ----------
function ambil_dan_validasi(): array
{
    $d = [
        'idn'              => trim($_POST['idn'] ?? ''),
        'fakultas'         => trim($_POST['fakultas'] ?? ''),
        'kode_pendaftaran' => strtoupper(trim($_POST['kode_pendaftaran'] ?? '')),
        'nama'             => trim($_POST['nama'] ?? ''),
        'jenis_kelamin'    => trim($_POST['jenis_kelamin'] ?? ''),
        'tempat_lahir'     => trim($_POST['tempat_lahir'] ?? ''),
        'tanggal_lahir'    => trim($_POST['tanggal_lahir'] ?? ''),
        'asal_sekolah'     => trim($_POST['asal_sekolah'] ?? ''),
        'pekerjaan_ortu'   => trim($_POST['pekerjaan_ortu'] ?? ''),
        'tempat_tes'       => trim($_POST['tempat_tes'] ?? ''),
        'nilai_indonesia'  => trim($_POST['nilai_indonesia'] ?? ''),
        'nilai_matematika' => trim($_POST['nilai_matematika'] ?? ''),
        'nilai_inggris'    => trim($_POST['nilai_inggris'] ?? ''),
        'nilai_umum'       => trim($_POST['nilai_umum'] ?? ''),
    ];
    $err = [];

    if (!preg_match('/^[A-Za-z0-9\-]{3,20}$/', $d['idn'])) {
        $err[] = 'IDN harus 3-20 karakter (huruf, angka, atau tanda minus).';
    }
    if (!in_array($d['fakultas'], DAFTAR_FAKULTAS, true)) {
        $err[] = 'Fakultas tidak valid.';
    }

    $info = parse_kode($d['kode_pendaftaran']);
    if ($info === null) {
        $err[] = 'Format Kode Pendaftaran tidak valid. Contoh: A2-101-9 (A/B/V + gelombang, nomor urut, bulan tes).';
    }

    if (mb_strlen($d['nama']) < 3 || mb_strlen($d['nama']) > 100 || !preg_match("/^[\p{L} .,'\-]+$/u", $d['nama'])) {
        $err[] = 'Nama harus 3-100 karakter dan hanya berisi huruf.';
    }
    if (!in_array($d['jenis_kelamin'], ['Laki-Laki', 'Perempuan'], true)) {
        $err[] = 'Jenis kelamin wajib dipilih.';
    }
    if ($d['tempat_lahir'] === '' || mb_strlen($d['tempat_lahir']) > 50) {
        $err[] = 'Tempat lahir wajib diisi (maksimal 50 karakter).';
    }

    $tgl = DateTime::createFromFormat('Y-m-d', $d['tanggal_lahir']);
    if (!$tgl || $tgl->format('Y-m-d') !== $d['tanggal_lahir']) {
        $err[] = 'Tanggal lahir tidak valid.';
    } elseif ($tgl > new DateTime('today')) {
        $err[] = 'Tanggal lahir tidak boleh di masa depan.';
    }

    if ($d['asal_sekolah'] === '' || mb_strlen($d['asal_sekolah']) > 100) {
        $err[] = 'Asal sekolah wajib diisi (maksimal 100 karakter).';
    }
    if ($d['pekerjaan_ortu'] === '' || mb_strlen($d['pekerjaan_ortu']) > 50) {
        $err[] = 'Pekerjaan orang tua wajib diisi (maksimal 50 karakter).';
    }

    if (!in_array($d['tempat_tes'], DAFTAR_TEMPAT_TES, true)) {
        $err[] = 'Tempat tes tidak valid.';
    } elseif ($info !== null && $info['tempat'] !== $d['tempat_tes']) {
        $err[] = 'Tempat tes tidak sesuai dengan kode pendaftaran (kode ' . $info['kode'] . ' = ' . $info['tempat'] . ').';
    }

    $labelNilai = [
        'nilai_indonesia'  => 'Nilai Bahasa Indonesia',
        'nilai_matematika' => 'Nilai Matematika',
        'nilai_inggris'    => 'Nilai Bahasa Inggris',
        'nilai_umum'       => 'Nilai Pendidikan Umum',
    ];
    foreach ($labelNilai as $f => $label) {
        if (!is_numeric($d[$f]) || (float)$d[$f] < 0 || (float)$d[$f] > 100) {
            $err[] = "$label harus berupa angka 0 - 100.";
        }
    }

    return [$d, $err];
}

// Kembali ke form dengan pesan error + isian sebelumnya
function kembali_dengan_error(array $data, array $err, ?int $id = null): void
{
    $_SESSION['old'] = $data;
    set_flash('error', 'Data tidak disimpan. Periksa kembali isian Anda.', $err);
    header('Location: index.php' . ($id ? '?edit=' . $id : ''));
    exit;
}

// Cek duplikat IDN / kode (kecuali record dengan id tertentu)
function cek_duplikat(mysqli $conn, array $d, int $exceptId = 0): array
{
    $err = [];
    $stmt = $conn->prepare('SELECT idn, kode_pendaftaran FROM calon_mhs WHERE (idn = ? OR kode_pendaftaran = ?) AND id <> ?');
    $stmt->bind_param('ssi', $d['idn'], $d['kode_pendaftaran'], $exceptId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        if ($r['idn'] === $d['idn']) $err['idn'] = 'IDN sudah terdaftar.';
        if ($r['kode_pendaftaran'] === $d['kode_pendaftaran']) $err['kode'] = 'Kode Pendaftaran sudah digunakan.';
    }
    $stmt->close();
    return array_values($err);
}

try {
    // ===================== TAMBAH =====================
    if ($aksi === 'tambah') {
        [$d, $err] = ambil_dan_validasi();
        if (!$err) $err = cek_duplikat($conn, $d);
        if ($err) kembali_dengan_error($d, $err);

        $sql = 'INSERT INTO calon_mhs
                (idn, fakultas, kode_pendaftaran, nama, jenis_kelamin, tempat_lahir, tanggal_lahir,
                 asal_sekolah, pekerjaan_ortu, tempat_tes,
                 nilai_indonesia, nilai_matematika, nilai_inggris, nilai_umum)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
        $stmt = $conn->prepare($sql);
        $ni = (float)$d['nilai_indonesia'];
        $nm = (float)$d['nilai_matematika'];
        $ng = (float)$d['nilai_inggris'];
        $nu = (float)$d['nilai_umum'];
        $stmt->bind_param('ssssssssssdddd',
            $d['idn'], $d['fakultas'], $d['kode_pendaftaran'], $d['nama'], $d['jenis_kelamin'],
            $d['tempat_lahir'], $d['tanggal_lahir'], $d['asal_sekolah'], $d['pekerjaan_ortu'],
            $d['tempat_tes'], $ni, $nm, $ng, $nu);
        $stmt->execute();
        $stmt->close();

        unset($_SESSION['old']);
        set_flash('success', 'Data pendaftar "' . $d['nama'] . '" berhasil disimpan.');
        header('Location: index.php');
        exit;
    }

    // ===================== EDIT =====================
    if ($aksi === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            set_flash('error', 'ID data tidak valid.');
            header('Location: index.php');
            exit;
        }
        [$d, $err] = ambil_dan_validasi();
        if (!$err) $err = cek_duplikat($conn, $d, $id);
        if ($err) kembali_dengan_error($d, $err, $id);

        $sql = 'UPDATE calon_mhs SET
                idn=?, fakultas=?, kode_pendaftaran=?, nama=?, jenis_kelamin=?, tempat_lahir=?,
                tanggal_lahir=?, asal_sekolah=?, pekerjaan_ortu=?, tempat_tes=?,
                nilai_indonesia=?, nilai_matematika=?, nilai_inggris=?, nilai_umum=?
                WHERE id=?';
        $stmt = $conn->prepare($sql);
        $ni = (float)$d['nilai_indonesia'];
        $nm = (float)$d['nilai_matematika'];
        $ng = (float)$d['nilai_inggris'];
        $nu = (float)$d['nilai_umum'];
        $stmt->bind_param('ssssssssssddddi',
            $d['idn'], $d['fakultas'], $d['kode_pendaftaran'], $d['nama'], $d['jenis_kelamin'],
            $d['tempat_lahir'], $d['tanggal_lahir'], $d['asal_sekolah'], $d['pekerjaan_ortu'],
            $d['tempat_tes'], $ni, $nm, $ng, $nu, $id);
        $stmt->execute();
        $stmt->close();

        unset($_SESSION['old']);
        set_flash('success', 'Data pendaftar "' . $d['nama'] . '" berhasil diperbarui.');
        header('Location: index.php');
        exit;
    }

    // ===================== HAPUS =====================
    if ($aksi === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $conn->prepare('DELETE FROM calon_mhs WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $terhapus = $stmt->affected_rows;
        $stmt->close();

        if ($terhapus > 0) {
            set_flash('success', 'Data pendaftar berhasil dihapus.');
        } else {
            set_flash('error', 'Data tidak ditemukan atau sudah dihapus.');
        }
        header('Location: index.php');
        exit;
    }

    set_flash('error', 'Aksi tidak dikenal.');
    header('Location: index.php');
    exit;

} catch (mysqli_sql_exception $e) {
    error_log('Error proses.php: ' . $e->getMessage());
    if ($e->getCode() === 1062) {
        set_flash('error', 'Gagal menyimpan: IDN atau Kode Pendaftaran sudah terdaftar.');
    } else {
        set_flash('error', 'Terjadi kesalahan pada database. Silakan coba lagi.');
    }
    header('Location: index.php');
    exit;
}