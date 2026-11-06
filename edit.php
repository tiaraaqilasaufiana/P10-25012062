<?php
require 'koneksi.php';
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id < 1) {
 http_response_code(400);
 exit('ID produk tidak valid.');
}
$ambil = mysqli_prepare($koneksi, 'SELECT * FROM produk WHERE id_produk = ?');
mysqli_stmt_bind_param($ambil, 'i', $id);
mysqli_stmt_execute($ambil);
$hasilProduk = mysqli_stmt_get_result($ambil);
$produk = mysqli_fetch_assoc($hasilProduk);
mysqli_stmt_close($ambil);
if (!$produk) {
 http_response_code(404);
 exit('Produk tidak ditemukan.');
}
$kategori = mysqli_query(
 $koneksi,
 'SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori'
);
$error = '';
$nama = trim($_POST['nama_produk'] ?? $produk['nama_produk']);
$hargaInput = $_POST['harga'] ?? $produk['harga'];
$stokInput = $_POST['stok'] ?? $produk['stok'];
$kategoriInput = $_POST['id_kategori'] ?? $produk['id_kategori'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 $harga = filter_var($hargaInput, FILTER_VALIDATE_FLOAT);
 $stok = filter_var($stokInput, FILTER_VALIDATE_INT);
 $idKategori = filter_var($kategoriInput, FILTER_VALIDATE_INT);
 if ($nama === '' || $harga === false || $harga <= 0 ||
 $stok === false || $stok < 0 || $idKategori === false || $idKategori < 1) {
 $error = 'Isi semua data dengan benar.';
 } else {
 $cek = mysqli_prepare($koneksi, 'SELECT COUNT(*) FROM kategori WHERE id_kategori = ?');
 mysqli_stmt_bind_param($cek, 'i', $idKategori);
 mysqli_stmt_execute($cek);
 mysqli_stmt_bind_result($cek, $jumlahKategori);
 mysqli_stmt_fetch($cek);
 mysqli_stmt_close($cek);
 if ((int) $jumlahKategori === 0) {
 $error = 'Kategori tidak ditemukan.';
 } else {
 $sql = 'UPDATE produk SET id_kategori = ?, nama_produk = ?, harga = ?, stok = ?
 WHERE id_produk = ?';
 $stmt = mysqli_prepare($koneksi, $sql);
 mysqli_stmt_bind_param($stmt, 'isdii', $idKategori, $nama, $harga, $stok, $id);
 mysqli_stmt_execute($stmt);
 mysqli_stmt_close($stmt);
 header('Location: index.php');
 exit;
 }
 }
}
?>
<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><title>Edit Produk</title></head>
<body>
<h1>Edit Produk</h1>
<?php if ($error !== ''): ?><p><?= e($error) ?></p><?php endif; ?>
<form method="post">
 <p>Nama: <input name="nama_produk" value="<?= e($nama) ?>" required></p>
 <p>Harga: <input name="harga" type="number" min="0.01" step="0.01"
 value="<?= e($hargaInput) ?>" required></p>
 <p>Stok: <input name="stok" type="number" min="0" step="1"
 value="<?= e($stokInput) ?>" required></p>
 <p>Kategori:
 <select name="id_kategori" required>
 <?php while ($item = mysqli_fetch_assoc($kategori)): ?>
 <?php $terpilih = (string) $kategoriInput === (string) $item['id_kategori']; ?>
 <option value="<?= e($item['id_kategori']) ?>"
 <?= $terpilih ? 'selected' : '' ?>>
 <?= e($item['nama_kategori']) ?>
 </option>
 <?php endwhile; ?>
 </select>
 </p>
 <button type="submit">Simpan perubahan</button>
</form>
<p><a href="index.php">Kembali</a></p>
</body>
</html>
