<?php

include 'securimage.php';

$img = new Securimage();

header('Content-type: audio/x-wav');
header('Content-Disposition: attachment; name="securimage.wav"');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Expires: Sun, 1 Jan 2000 12:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT');

echo $img->getAudibleCode();
exit;

?>