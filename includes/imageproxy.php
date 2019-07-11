<?php
require_once '../../../../wp-config.php';

$kapi = KuvapankkiAPI::get_instance();
$filedetails = explode(';', base64_decode($_GET['fd']));
$thumbnail = isset($_GET['thumbnail']) && $_GET['thumbnail'] == true;

$id = $filedetails[0];
$hashedName = $filedetails[1];
$mime = $filedetails[2];

$data = null;
if ($thumbnail) {
    header('Content-Type: image/jpeg');

    $data = $kapi->file_thumbnail($id);
    $image = imagecreatefromstring($data);
    imagejpeg($image, null, 100);
    imagedestroy($image);
} else {
    $data = $kapi->file($id);
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . strlen($data));
    
    echo $data;
}