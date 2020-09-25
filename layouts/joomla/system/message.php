<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$msgList = $displayData['msgList'];

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

$output = null;

if (is_array($msgList) && !empty($msgList)) :
	foreach ($msgList as $type => $msgs) :
		$output = LayoutHelper::render('joomla.system.joomla-alert', ['alertType' => $alert[$type], 'type' => $type, 'msg' => $msgs]);
	endforeach;
endif;
?>
<div id="system-message-container" aria-live="polite">
	<div id="system-message"><?php echo $output; ?></div>
</div>
