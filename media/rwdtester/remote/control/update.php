<?php

$filename = '../url.txt';
$pattern = '/(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/';

function err($msg) {
  header('HTTP/1.0 400 Bad Request');
  echo $msg;
  exit( E_ERROR );
}

if (empty($_POST['url'])) {
  err('Error: No url given.');
}
if (preg_match($pattern, $_POST['url']) !== 1) {
  err('Error: Invalid url given.');
}
if (!is_writable($filename)) {
  err('Error: url.txt not writable by webserver.');
}
if (file_put_contents($filename, $_POST['url']) === false) {
  err('Error: Unable to save to url.txt.');
}

echo 'Success! Go check your devices.';
