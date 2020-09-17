<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die();

defined('AKEEBA_COMMON_WRONGPHP') || define('AKEEBA_COMMON_WRONGPHP', 1);

JDEBUG ? (defined('AKEEBADEBUG') || define('AKEEBADEBUG', 1)) : null;

$minPHPVersion         = '7.1.0';
$recommendedPHPVersion = '7.3';
$softwareName          = 'Akeeba Backup';
$silentResults         = true;

if (!require_once(JPATH_COMPONENT_ADMINISTRATOR . '/View/wrongphp.php'))
{
	die;
}

if (!defined('FOF30_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof30/include.php'))
{
	throw new RuntimeException('FOF 3.0 is not installed', 500);
}

define('AKEEBA_CACERT_PEM', JPATH_LIBRARIES . '/src/Http/Transport/cacert.pem');

FOF30\Container\Container::getInstance('com_akeeba')->dispatcher->dispatch();
