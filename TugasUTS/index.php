<?php
// =====================================================
// index.php - Form, Tabel Data, Perhitungan Nilai, Rekapitulasi
// =====================================================
require 'koneksi.php';

// ---------- Pesan flash & isian lama ----------
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$old = $_SESSION['old'] ?? null;
unset($_SESSION['old']);

// ---------- Mode edit ----------
$editData = null;
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
if ($editId > 0) {
    $stmt = $conn->prepare('SELECT * FROM calon_mhs WHERE id = ?');
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$editData) {
        $flash = ['type' => 'error', 'msg' => 'Data yang akan diedit tidak ditemukan.', 'errors' => []];
        $editId = 0;
    }
}
$modeEdit = $editData !== null;

// Nilai yang ditampilkan di form: prioritas isian lama (error) > data edit > kosong
function val(string $f, ?array $old, ?array $edit, string $default = ''): string
{
    if ($old !== null && isset($old[$f])) return (string)$old[$f];
    if ($edit !== null && isset($edit[$f])) return (string)$edit[$f];
    return $default;
}

// ---------- Ambil seluruh data ----------
$rows = [];
$res = $conn->query('SELECT * FROM calon_mhs ORDER BY id ASC');
while ($r = $res->fetch_assoc()) {
    $r['rata'] = hitung_rata((float)$r['nilai_matematika'], (float)$r['nilai_inggris'], (float)$r['nilai_umum']);
    $r['status'] = tentukan_status($r['rata']);
    $r['info_kode'] = parse_kode($r['kode_pendaftaran']);
    $rows[] = $r;
}

// ---------- Rekapitulasi (dihitung dinamis dari data DB) ----------
$jumlahPendaftar = count($rows);
$jumlahLulus = 0;
$jumlahTidakLulus = 0;
$jumlahCadangan = 0;
foreach ($rows as $r) {
    if ($r['status'] === 'Lulus') $jumlahLulus++;
    elseif ($r['status'] === 'Tidak Lulus') $jumlahTidakLulus++;
    else $jumlahCadangan++; // Cadangan tidak masuk Lulus maupun Tidak Lulus
}

$vJk = val('jenis_kelamin', $old, $editData);
$vFak = val('fakultas', $old, $editData);
$vTes = val('tempat_tes', $old, $editData);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pendaftaran Mahasiswa Baru</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <h1>Pendaftaran Mahasiswa Baru</h1>
    <p>Input data calon mahasiswa, hitung nilai, dan lihat hasil seleksi</p>
</header>

<main class="container">

    <?php if ($flash): ?>
        <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-error' ?>" id="alert">
            <strong><?= e($flash['msg']) ?></strong>
            <?php if (!empty($flash['errors'])): ?>
                <ul>
                    <?php foreach ($flash['errors'] as $er): ?>
                        <li><?= e($er) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- ===================== FORM ===================== -->
    <section class="card">
        <h2><?= $modeEdit ? 'Edit Data Pendaftar' : 'Form Pendaftaran' ?></h2>

        <form action="proses.php" method="post" id="formDaftar" novalidate>
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="aksi" value="<?= $modeEdit ? 'edit' : 'tambah' ?>">
            <?php if ($modeEdit): ?>
                <input type="hidden" name="id" value="<?= (int)$editData['id'] ?>">
            <?php endif; ?>

            <div class="grid">
                <div class="field">
                    <label for="idn">IDN / Pendaftaran</label>
                    <input type="text" id="idn" name="idn" maxlength="20" required
                           value="<?= e(val('idn', $old, $editData)) ?>" placeholder="contoh: IDN005">
                </div>

                <div class="field">
                    <label for="fakultas">Fakultas</label>
                    <select id="fakultas" name="fakultas" required>
                        <option value="">-- Pilih Fakultas --</option>
                        <?php foreach (DAFTAR_FAKULTAS as $f): ?>
                            <option value="<?= e($f) ?>" <?= $vFak === $f ? 'selected' : '' ?>><?= e($f) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label for="kode_pendaftaran">Kode Pendaftaran</label>
                    <input type="text" id="kode_pendaftaran" name="kode_pendaftaran" maxlength="20" required
                           value="<?= e(val('kode_pendaftaran', $old, $editData)) ?>" placeholder="contoh: A2-101-9">
                    <small id="infoKode" class="hint">Format: [A/B/V][Gelombang]-[No Urut]-[Bulan]</small>
                </div>

                <div class="field">
                    <label for="nama">Nama Pendaftar</label>
                    <input type="text" id="nama" name="nama" maxlength="100" required
                           value="<?= e(val('nama', $old, $editData)) ?>">
                </div>

                <div class="field">
                    <span class="label">Jenis Kelamin</span>
                    <div class="radio-group">
                        <label><input type="radio" name="jenis_kelamin" value="Laki-Laki" <?= $vJk === 'Laki-Laki' ? 'checked' : '' ?> required> Laki-Laki</label>
                        <label><input type="radio" name="jenis_kelamin" value="Perempuan" <?= $vJk === 'Perempuan' ? 'checked' : '' ?>> Perempuan</label>
                    </div>
                </div>

                <div class="field">
                    <label for="tempat_lahir">Tempat Lahir</label>
                    <input type="text" id="tempat_lahir" name="tempat_lahir" maxlength="50" required
                           value="<?= e(val('tempat_lahir', $old, $editData)) ?>">
                </div>

                <div class="field">
                    <label for="tanggal_lahir">Tanggal Lahir</label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir" required max="<?= date('Y-m-d') ?>"
                           value="<?= e(val('tanggal_lahir', $old, $editData)) ?>">
                </div>

                <div class="field">
                    <label for="asal_sekolah">Asal Sekolah</label>
                    <input type="text" id="asal_sekolah" name="asal_sekolah" maxlength="100" required
                           value="<?= e(val('asal_sekolah', $old, $editData)) ?>">
                </div>

                <div class="field">
                    <label for="pekerjaan_ortu">Pekerjaan Orang Tua</label>
                    <input type="text" id="pekerjaan_ortu" name="pekerjaan_ortu" maxlength="50" required
                           list="listPekerjaan" value="<?= e(val('pekerjaan_ortu', $old, $editData)) ?>">
                    <datalist id="listPekerjaan">
                        <option value="PNS"><option value="Wiraswasta"><option value="Karyawan Swasta">
                        <option value="Petani"><option value="Guru"><option value="TNI/Polri"><option value="Pedagang">
                    </datalist>
                </div>

                <div class="field">
                    <label for="tempat_tes">Tempat Tes</label>
                    <select id="tempat_tes" name="tempat_tes" required>
                        <option value="">-- Pilih Tempat Tes --</option>
                        <?php foreach (DAFTAR_TEMPAT_TES as $t): ?>
                            <option value="<?= e($t) ?>" <?= $vTes === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label for="nilai_indonesia">Nilai Bahasa Indonesia</label>
                    <input type="number" id="nilai_indonesia" name="nilai_indonesia" min="0" max="100" step="0.01" required
                           value="<?= e(val('nilai_indonesia', $old, $editData)) ?>">
                </div>

                <div class="field">
                    <label for="nilai_matematika">Nilai Matematika</label>
                    <input type="number" id="nilai_matematika" name="nilai_matematika" min="0" max="100" step="0.01" required
                           value="<?= e(val('nilai_matematika', $old, $editData)) ?>">
                </div>

                <div class="field">
                    <label for="nilai_inggris">Nilai Bahasa Inggris</label>
                    <input type="number" id="nilai_inggris" name="nilai_inggris" min="0" max="100" step="0.01" required
                           value="<?= e(val('nilai_inggris', $old, $editData)) ?>">
                </div>

                <div class="field">
                    <label for="nilai_umum">Pendidikan Umum</label>
                    <input type="number" id="nilai_umum" name="nilai_umum" min="0" max="100" step="0.01" required
                           value="<?= e(val('nilai_umum', $old, $editData)) ?>">
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary"><?= $modeEdit ? 'Simpan Perubahan' : 'Simpan Pendaftaran' ?></button>
                <?php if ($modeEdit): ?>
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                <?php else: ?>
                    <button type="reset" class="btn btn-secondary">Reset</button>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <!-- ===================== TABEL DATA ===================== -->
    <section class="card">
        <h2>Data Pendaftar</h2>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>No</th>
                    <th>Kode Pendaftar</th>
                    <th>Nama Pendaftar</th>
                    <th>Tempat Lahir</th>
                    <th>Tanggal Lahir</th>
                    <th>Pekerjaan Orang Tua</th>
                    <th>Tempat Tes</th>
                    <th>Bahasa Indonesia</th>
                    <th>Matematika</th>
                    <th>Bahasa Inggris</th>
                    <th>Umum</th>
                    <th>Nilai Rata-Rata</th>
                    <th>Keterangan</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!$rows): ?>
                    <tr><td colspan="14" class="empty">Belum ada data pendaftar.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $i => $r):
                    $kls = $r['status'] === 'Lulus' ? 'badge-lulus' : ($r['status'] === 'Cadangan' ? 'badge-cadangan' : 'badge-tidak');
                    $ik = $r['info_kode'];
                ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td>
                            <strong><?= e($r['kode_pendaftaran']) ?></strong>
                            <?php if ($ik): ?>
                                <small class="sub"><?= e($ik['tempat']) ?>, Gelombang <?= (int)$ik['gelombang'] ?><br>Bln Tes: <?= e($ik['bulan']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= e($r['nama']) ?></td>
                        <td><?= e($r['tempat_lahir']) ?></td>
                        <td><?= e(date('d-m-Y', strtotime($r['tanggal_lahir']))) ?></td>
                        <td><?= e($r['pekerjaan_ortu']) ?></td>
                        <td><?= e($r['tempat_tes']) ?></td>
                        <td class="num"><?= e(number_format((float)$r['nilai_indonesia'], 2)) ?></td>
                        <td class="num"><?= e(number_format((float)$r['nilai_matematika'], 2)) ?></td>
                        <td class="num"><?= e(number_format((float)$r['nilai_inggris'], 2)) ?></td>
                        <td class="num"><?= e(number_format((float)$r['nilai_umum'], 2)) ?></td>
                        <td class="num"><strong><?= e(number_format($r['rata'], 2)) ?></strong></td>
                        <td><span class="badge <?= $kls ?>"><?= e($r['status']) ?></span></td>
                        <td class="aksi">
                            <a href="index.php?edit=<?= (int)$r['id'] ?>#formDaftar" class="btn btn-sm btn-edit">Edit</a>
                            <form action="proses.php" method="post" class="inline"
                                  onsubmit="return confirm('Yakin ingin menghapus data <?= e(addslashes($r['nama'])) ?>?');">
                                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="aksi" value="hapus">
                                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-hapus">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="note">Nilai rata-rata = (Matematika + Bahasa Inggris + Umum) / 3.
            Lulus ≥ 70 &nbsp;|&nbsp; Cadangan 60 – &lt; 70 &nbsp;|&nbsp; Tidak Lulus &lt; 60.</p>
    </section>

    <!-- ===================== REKAPITULASI ===================== -->
    <section class="card">
        <h2>Rekapitulasi</h2>
        <div class="rekap">
            <div class="rekap-item">
                <span>Jumlah Pendaftar</span>
                <strong><?= $jumlahPendaftar ?></strong>
            </div>
            <div class="rekap-item lulus">
                <span>Jumlah Peserta Lulus</span>
                <strong><?= $jumlahLulus ?></strong>
            </div>
            <div class="rekap-item tidak">
                <span>Jumlah Peserta Tidak Lulus</span>
                <strong><?= $jumlahTidakLulus ?></strong>
            </div>
        </div>
        <p class="note">Peserta berstatus Cadangan (<?= $jumlahCadangan ?> orang) tidak dihitung sebagai Lulus maupun Tidak Lulus.</p>
    </section>
</main>

<footer class="footer">Aplikasi Pendaftaran Mahasiswa Baru &copy; <?= date('Y') ?></footer>

<script>
// Peta aturan kode pendaftaran (sama dengan parse_kode() di koneksi.php)
const TEMPAT = {A: 'Gedung A', B: 'Gedung B', V: 'Viktor'};
const BULAN = {'1':'Januari','2':'Februari','3':'Maret','4':'April','5':'Mei','6':'Juni','7':'Juli',
               '8':'Agustus','9':'September','A':'Oktober','B':'November','C':'Desember'};

const kodeEl = document.getElementById('kode_pendaftaran');
const infoEl = document.getElementById('infoKode');
const tesEl = document.getElementById('tempat_tes');

function cekKode() {
    kodeEl.value = kodeEl.value.toUpperCase();
    const m = kodeEl.value.trim().match(/^([ABV])([1-9])-(\d{1,6})-([1-9ABC])$/);
    if (!m) {
        infoEl.textContent = kodeEl.value ? 'Format kode belum valid. Contoh: A2-101-9' : 'Format: [A/B/V][Gelombang]-[No Urut]-[Bulan]';
        infoEl.className = kodeEl.value ? 'hint hint-error' : 'hint';
        return;
    }
    infoEl.textContent = TEMPAT[m[1]] + ', Gelombang ' + m[2] + ' | Bln Tes: ' + BULAN[m[4]];
    infoEl.className = 'hint hint-ok';
    tesEl.value = TEMPAT[m[1]];   // sinkronkan tempat tes dengan kode
}
kodeEl.addEventListener('input', cekKode);
if (kodeEl.value) cekKode();

// Validasi sederhana di sisi klien (validasi utama tetap di server)
document.getElementById('formDaftar').addEventListener('submit', function (e) {
    const angka = ['nilai_indonesia', 'nilai_matematika', 'nilai_inggris', 'nilai_umum'];
    for (const id of angka) {
        const v = parseFloat(document.getElementById(id).value);
        if (isNaN(v) || v < 0 || v > 100) {
            alert('Semua nilai harus berupa angka 0 - 100.');
            e.preventDefault();
            return;
        }
    }
    if (!this.checkValidity()) {
        e.preventDefault();
        this.reportValidity();
    }
});
</script>
</body>
</html>