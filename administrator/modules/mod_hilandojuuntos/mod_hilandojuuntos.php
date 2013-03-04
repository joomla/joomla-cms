<?php
/**
 * @version		Hilos Juuntos v1.0.1
 * @copyleft	Comunidad Juuntos - juuntos.net
 * @licencia	GNU General Public License version 2 or later; see LICENSE.txt
 */
// Acceso restringido
defined('_JEXEC') or die;

require_once dirname(__FILE__).'/helper.php';

// Para testing!
$rssurl0	= $params->get('rssurl0', '');
$rssurl1	= $params->get('rssurl1', '');
$rssurl2	= $params->get('rssurl2', '');
$rssurl3	= $params->get('rssurl3', '');
$rssurl4	= $params->get('rssurl4', '');


// Sumando enlaces
$rssurl		= $rssurl0.$rssurl1.$rssurl2.$rssurl3.$rssurl4;
				

$cacheDir = JPATH_CACHE;
if (!is_writable($cacheDir))
{
	echo '<div style="color:red;font-weight:bold">';
	echo JText::_('MOD_HILANDOJUUNTOS_ERR_CACHE');
	echo '</div>';
	return;
}

// Verifico que al menos haya una URL para hilos
if (empty ($rssurl))
{
	echo '<div style="color:green;font-weight:bold">';
	echo JText::_('MOD_HILANDOJUUNTOS_ERR_NO_URL');
	echo '</div>';
	return;
}

require JModuleHelper::getLayoutPath('mod_hilandojuuntos');
