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

// Alerts progressive enhancement
Factory::getDocument()->getWebAssetManager()
	->useStyle('webcomponent.joomla-alert')
	->useScript('webcomponent.joomla-alert');

?>
<div id="system-message-container" aria-live="polite">
	<div id="system-message">
		<?php if (is_array($msgList) && !empty($msgList)) : ?>
			<?php foreach ($msgList as $type => $msgs) : ?>
				<joomla-alert type="<?php echo $alert[$type] ?? $type; ?>" dismiss="true">
					<?php if (!empty($msgs)) : ?>
						<div class="alert-heading"><?php echo Text::_($type); ?></div>
						<div>
							<?php foreach ($msgs as $msg) : ?>
								<p><?php echo $msg; ?></p>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</joomla-alert>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</div>
