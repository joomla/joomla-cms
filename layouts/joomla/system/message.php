<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$msgList = $displayData['msgList'];

$alert = [
	JApplicationCms::MSG_EMERGENCY => 'danger',
	JApplicationCms::MSG_ALERT     => 'danger',
	JApplicationCms::MSG_CRITICAL  => 'danger',
	JApplicationCms::MSG_ERROR     => 'danger',
	JApplicationCms::MSG_WARNING   => 'warning',
	JApplicationCms::MSG_NOTICE    => 'info',
	JApplicationCms::MSG_INFO      => 'info',
	JApplicationCms::MSG_DEBUG     => 'info',
];

?>
<div id="system-message-container">
	<div id="system-message">
		<?php if (is_array($msgList) && !empty($msgList)) : ?>
			<?php foreach ($msgList as $type => $msgs) : ?>
				<joomla-alert level="<?php echo isset($alert[$type]) ? $alert[$type] : $type; ?>" dismiss="true">
					<?php if (!empty($msgs)) : ?>
						<h4><?php echo JText::_($type); ?></h4>
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