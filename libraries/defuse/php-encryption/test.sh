#!/bin/bash

echo "Normal"
echo "--------------------------------------------------"
php -d mbstring.func_overload=0 tests/runtime.php
if [ $? -ne 0 ]; then
    echo "FAIL."
    exit 1
fi
echo "--------------------------------------------------"

echo ""

echo "Multibyte"
echo "--------------------------------------------------"
php -d mbstring.func_overload=7 tests/runtime.php
if [ $? -ne 0 ]; then
    echo "FAIL."
    exit 1
fi
echo "--------------------------------------------------"

echo ""

if [ -z "$(php Crypto.php)" ]; then
    echo "PASS: Crypto.php output is empty."
else
    echo "FAIL: Crypto.php output is not empty."
    exit 1
fi
