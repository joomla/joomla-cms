<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.Isis
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$msgList = $displayData['msgList'];

$alert = array('error' => 'alert-error', 'warning' => '', 'notice' => 'alert-info', 'message' => 'alert-success');
?>
<div id="system-message-container">
	<?php if (is_array($msgList) && $msgList) : ?>
		<?php foreach ($msgList as $type => $msgs) : ?>
			<div class="alert <?php echo isset($alert[$type]) ? $alert[$type] : 'alert-' . $type; ?>">
				<button type="button" class="close" data-dismiss="alert">&times;</button>
				<?php if (!empty($msgs)) : ?>
					<h4 class="alert-heading"><?php echo JText::_($type); ?></h4>
					<?php foreach ($msgs as $msg) : ?>
						<div class="alert-message"><?php echo $msg; ?></div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>
