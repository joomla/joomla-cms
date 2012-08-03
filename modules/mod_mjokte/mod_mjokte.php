<?php


// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


require_once (dirname(__FILE__).DS.'helper.php');

$mjokte_output = modMJOKTE::createOutput();
require(JModuleHelper::getLayoutPath('mod_mjokte'));
