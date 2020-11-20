@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../leafo/lessphp/plessc
php "%BIN_TARGET%" %*
