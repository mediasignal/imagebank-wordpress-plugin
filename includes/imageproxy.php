<?php
require_once '../../../../wp-config.php';

header('Content-Type: image/jpeg');
$kapi = KuvapankkiAPI::get_instance();
$id = $_GET['id'];
$thumbnail = isset($_GET['thumbnail']) && $_GET['thumbnail'] == true;

$data = null;
if ($thumbnail) {
    $data = $kapi->file_thumbnail($id);
} else {
    $data = $kapi->file($id);
}

$image = imagecreatefromstring($data);
imagejpeg($image, null, 100);
imagedestroy($image);