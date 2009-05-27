<?php
$path_extra = dirname(__FILE__);
$path = ini_get('include_path');
$path = $path_extra . DS . $path;
ini_set('include_path', $path);


/**
 * Require the OpenID consumer code.
 */
require_once "Auth/OpenID/Consumer.php";

/**
 * Require the "file store" module, which we'll need to store
 * OpenID information.
 */
require_once "Auth/OpenID/FileStore.php";

/**
 * Require the Simple Registration extension API.
 */
require_once "Auth/OpenID/SReg.php";

/**
 * Require the PAPE extension module.
 */
require_once "Auth/OpenID/PAPE.php";

