<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

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

$icon = [
	CMSApplication::MSG_EMERGENCY => 'fa fa-exclamation-triangle',
	CMSApplication::MSG_ALERT     => 'fa fa-exclamation-triangle',
	CMSApplication::MSG_CRITICAL  => 'fa fa-exclamation-triangle',
	CMSApplication::MSG_ERROR     => 'fa fa-exclamation-triangle',
	CMSApplication::MSG_WARNING   => 'fa fa-exclamation-circle',
	CMSApplication::MSG_NOTICE    => 'fa fa-info-circle',
	CMSApplication::MSG_INFO      => 'fa fa-info-circle',
	CMSApplication::MSG_DEBUG     => 'fa fa-info-circle',
	'message'                     => 'fa fa-info-circle'
];

// Alerts progressive enhancement
HTMLHelper::_('webcomponent', 'vendor/joomla-custom-elements/joomla-alert.min.js', ['version' => 'auto', 'relative' => true]);
?>
<div id="system-message-container">
	<div id="system-message">
		<?php if (is_array($msgList) && !empty($msgList)) : ?>
			<?php foreach ($msgList as $type => $msgs) : ?>
				<joomla-alert type="<?php echo $alert[$type] ?? $type; ?>" dismiss="true">
					<?php if (!empty($msgs)) : ?>
						<div class="alert-heading">
							<span class="<?php echo $icon[$type]; ?>" aria-hidden="true"></span>
							<?php echo Text::_($type); ?>
						</div>
						<?php foreach ($msgs as $msg) : ?>
							<div><?php echo $msg; ?></div>
						<?php endforeach; ?>
					<?php endif; ?>
				</joomla-alert>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</div>
