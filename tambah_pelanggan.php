<?php
require 'koneksi.php';

$jenisPelanggan = mysqli_query(
    $koneksi,
    'SELECT id_jenis, jenis FROM jenis_pelanggan ORDER BY jenis'
);
$error = '';
$nama = trim($_POST['nama_pelanggan'] ?? '');
$jenisInput = $_POST['id_jenis'] ?? '';
$alamatInput = trim($_POST['alamat'] ?? '');
$handphoneInput = trim($_POST['no_handphone'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idJenis = filter_var($jenisInput, FILTER_VALIDATE_INT);
    $panjangNama = preg_match_all('/./us', $nama);
    $panjangHandphone = preg_match_all('/./us', $handphoneInput);

    if ($nama === '' || $panjangNama === false || $panjangNama > 100 ||
        $panjangHandphone === false || $panjangHandphone > 20 ||
        $idJenis === false || $idJenis < 1) {
        $error = 'Isi nama dan jenis pelanggan dengan benar. Nama maksimal 100 karakter dan nomor handphone maksimal 20 karakter.';
    } else {
        $cek = mysqli_prepare($koneksi, 'SELECT COUNT(*) FROM jenis_pelanggan WHERE id_jenis = ?');
        mysqli_stmt_bind_param($cek, 'i', $idJenis);
        mysqli_stmt_execute($cek);
        mysqli_stmt_bind_result($cek, $jumlahJenis);
        mysqli_stmt_fetch($cek);
        mysqli_stmt_close($cek);

        if ((int) $jumlahJenis === 0) {
            $error = 'Jenis pelanggan tidak ditemukan.';
        } else {
            $nama = trim($nama);
            $alamat = $alamatInput === '' ? null : $alamatInput;
            $handphone = $handphoneInput === '' ? null : $handphoneInput;
            $sql = 'INSERT INTO pelanggan (id_jenis, nama_pelanggan, alamat, no_handphone)
                    VALUES (?, ?, ?, ?)';
            $stmt = mysqli_prepare($koneksi, $sql);
            mysqli_stmt_bind_param($stmt, 'isss', $idJenis, $nama, $alamat, $handphone);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            header('Location: pelanggan.php');
            exit;
        }
    }
}
?>

<!doctype html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <title>Tambah Pelanggan</title>
    </head>
    <body>
        <h1>Tambah Pelanggan</h1>
        <?php if ($error !== ''): ?><p><?= e($error) ?></p><?php endif; ?>
        <form method="post">
            <p>Nama pelanggan:
                <input name="nama_pelanggan" maxlength="100" value="<?= e($nama) ?>" required>
            </p>
            <p>Jenis pelanggan:
                <select name="id_jenis" required>
                    <option value="">Pilih jenis pelanggan</option>
                    <?php while ($item = mysqli_fetch_assoc($jenisPelanggan)): ?>
                        <option value="<?= e($item['id_jenis']) ?>"
                            <?= (string) $jenisInput === (string) $item['id_jenis'] ? 'selected' : '' ?>>
                            <?= e($item['jenis']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </p>
            <p>Alamat:
                <textarea name="alamat"><?= e($alamatInput) ?></textarea>
            </p>
            <p>No. handphone:
                <input name="no_handphone" maxlength="20" value="<?= e($handphoneInput) ?>">
            </p>
            <button type="submit">Simpan</button>
        </form>
        <p><a href="pelanggan.php">Kembali</a></p>
    </body>
</html>
