<?php
require 'koneksi.php';

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id < 1) {
    http_response_code(400);
    exit('ID pelanggan tidak valid.');
}

$ambil = mysqli_prepare($koneksi, 'SELECT * FROM pelanggan WHERE id_pelanggan = ?');
mysqli_stmt_bind_param($ambil, 'i', $id);
mysqli_stmt_execute($ambil);
$hasilPelanggan = mysqli_stmt_get_result($ambil);
$pelanggan = mysqli_fetch_assoc($hasilPelanggan);
mysqli_stmt_close($ambil);
if (!$pelanggan) {
    http_response_code(404);
    exit('Pelanggan tidak ditemukan.');
}

$jenisPelanggan = mysqli_query(
    $koneksi,
    'SELECT id_jenis, jenis FROM jenis_pelanggan ORDER BY jenis'
);
$error = '';
$nama = trim($_POST['nama_pelanggan'] ?? $pelanggan['nama_pelanggan']);
$jenisInput = $_POST['id_jenis'] ?? $pelanggan['id_jenis'];
$alamatInput = trim($_POST['alamat'] ?? ($pelanggan['alamat'] ?? ''));
$handphoneInput = trim($_POST['no_handphone'] ?? ($pelanggan['no_handphone'] ?? ''));

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
            $alamat = $alamatInput === '' ? null : $alamatInput;
            $handphone = $handphoneInput === '' ? null : $handphoneInput;
            $sql = 'UPDATE pelanggan
                    SET id_jenis = ?, nama_pelanggan = ?, alamat = ?, no_handphone = ?
                    WHERE id_pelanggan = ?';
            $stmt = mysqli_prepare($koneksi, $sql);
            mysqli_stmt_bind_param($stmt, 'isssi', $idJenis, $nama, $alamat, $handphone, $id);
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
        <title>Edit Pelanggan</title>
    </head>
    <body>
        <h1>Edit Pelanggan</h1>
        <?php if ($error !== ''): ?><p><?= e($error) ?></p><?php endif; ?>
        <form method="post">
            <p>Nama pelanggan:
                <input name="nama_pelanggan" maxlength="100" value="<?= e($nama) ?>" required>
            </p>
            <p>Jenis pelanggan:
                <select name="id_jenis" required>
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
            <button type="submit">Simpan perubahan</button>
        </form>
        <p><a href="pelanggan.php">Kembali</a></p>
    </body>
</html>
