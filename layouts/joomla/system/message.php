<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Application\CMSApplication;
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
];

?>
<div id="system-message-container">
	<div id="system-message">
		<?php if (is_array($msgList) && !empty($msgList)) : ?>
			<?php foreach ($msgList as $type => $msgs) : ?>
				<joomla-alert level="<?php echo $alert[$type] ?? $type; ?>" dismiss="true">
					<?php if (!empty($msgs)) : ?>
						<h4><?php echo Text::_($type); ?></h4>
						<div>
							<?php foreach ($msgs as $msg) : ?>
								<div><?php echo $msg; ?></div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</joomla-alert>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</div>
