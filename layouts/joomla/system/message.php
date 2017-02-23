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
	JApplicationCms::MSG_EMERGENCY => 'alert-danger',
	JApplicationCms::MSG_ALERT     => 'alert-danger',
	JApplicationCms::MSG_CRITICAL  => 'alert-danger',
	JApplicationCms::MSG_ERROR     => 'alert-danger',
	JApplicationCms::MSG_WARNING   => 'alert-warning',
	JApplicationCms::MSG_NOTICE    => 'alert-info',
	JApplicationCms::MSG_INFO      => 'alert-info',
	JApplicationCms::MSG_DEBUG     => 'alert-info',
];

?>
<div id="system-message-container">
	<?php if (is_array($msgList) && !empty($msgList)) : ?>
		<div id="system-message">
			<?php foreach ($msgList as $type => $msgs) : ?>
				<div class="alert <?php echo isset($alert[$type]) ? $alert[$type] : 'alert-' . $type; ?>">
					<?php // This requires JS so we should add it trough JS. Progressive enhancement and stuff. ?>
					<a class="close" data-dismiss="alert">&times;</a>
					<?php if (!empty($msgs)) : ?>
						<h4 class="alert-heading"><?php echo JText::_($type); ?></h4>
						<div>
							<?php foreach ($msgs as $msg) : ?>
								<div><?php echo $msg; ?></div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
