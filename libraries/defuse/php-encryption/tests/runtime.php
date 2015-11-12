<?php

// Set the encoding to something more "challenging."
$ret = mb_internal_encoding('UTF-8');
if ($ret === FALSE) {
    echo "Couldn't set encoding.";
    exit(1);
}

// Dump out the settings / encoding for future reference.
$val = ini_get("mbstring.func_overload");
echo "Settings: \n";
echo "    func_overload: " . $val . "\n";
echo "    mb_internal_encoding(): " . mb_internal_encoding() . "\n";

// Perform the tests.
require_once('Crypto.php');
try {
    Crypto::RuntimeTest();
    echo "TEST PASSED!\n";
    exit(0);
} catch (CryptoTestFailedException $ex) {
    echo "TEST FAILED!\n";
    var_dump($ex);
    exit(1);
} catch (CannotPerformOperationException $ex) {
    echo "TEST FAILED\n";
    var_dump($ex);
    exit(1);
}

?>
