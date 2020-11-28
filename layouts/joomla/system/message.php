<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

$msgList   = $displayData['msgList'];
$msgOutput = '';

$alert = [
	CMSApplication::MSG_EMERGENCY => 'danger',
	CMSApplication::MSG_ALERT     => 'danger',
	CMSApplication::MSG_CRITICAL  => 'danger',
	CMSApplication::MSG_ERROR     => 'danger',
	CMSApplication::MSG_WARNING   => 'warning',
	CMSApplication::MSG_NOTICE    => 'info',
	CMSApplication::MSG_INFO      => 'info',
	CMSApplication::MSG_DEBUG     => 'info',
	'message'                     => 'success'
];

// Load JavaScript message titles
Text::script('ERROR');
Text::script('MESSAGE');
Text::script('NOTICE');
Text::script('WARNING');

// Load other Javascript message strings
Text::script('JCLOSE');
Text::script('JOK');
Text::script('JOPEN');

// Alerts progressive enhancement
Factory::getDocument()->getWebAssetManager()
	->useStyle('webcomponent.joomla-alert')
	->useScript('webcomponent.joomla-alert');

if (is_array($msgList) && !empty($msgList)) :
	foreach ($msgList as $type => $msgs) :
		$msgOutput .= '<joomla-alert type="' . ($alert[$type] ?? $type) . '" dismiss="true">';
		if (!empty($msgs)) :
			$msgOutput .= '<div class="alert-heading">';
			$msgOutput .= '<span class="' . $type . '"></span>';
			$msgOutput .= '<span class="sr-only">' . Text::_($type) . '</span>';
			$msgOutput .= '</div>';
			$msgOutput .= '<div class="alert-wrapper">';
			foreach ($msgs as $msg) :
				$msgOutput .= '<div class="alert-message">' . $msg . '</div>';
			endforeach;
			$msgOutput .= '</div>';
		endif;
		$msgOutput .= '</joomla-alert>';
	endforeach;
endif;
?>
<div id="system-message-container" aria-live="polite"><?php echo $msgOutput; ?></div>
