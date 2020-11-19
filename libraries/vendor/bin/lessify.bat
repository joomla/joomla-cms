@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../leafo/lessphp/lessify
php "%BIN_TARGET%" %*
