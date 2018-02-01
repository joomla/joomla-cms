@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../defuse/php-encryption/bin/generate-defuse-key
php "%BIN_TARGET%" %*
