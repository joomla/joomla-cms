<?php

// Include SimplePie
// Located in the parent directory
include_once('../simplepie.inc');

// Initialize SimplePie
$image = new SimplePie();

// Tell it to handle images sent via the 'i' parameter.
$image->bypass_image_hotlink('i');

// Initialize
$image->init();

?>