<?php
require 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Gunakan tombol Hapus pada daftar pelanggan.');
}

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id < 1) {
    http_response_code(400);
    exit('ID pelanggan tidak valid.');
}

$stmt = mysqli_prepare($koneksi, 'DELETE FROM pelanggan WHERE id_pelanggan = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
header('Location: pelanggan.php');
exit;
