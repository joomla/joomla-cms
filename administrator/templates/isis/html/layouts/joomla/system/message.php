<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.Isis
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$msgList    = $displayData['msgQueue'];
$alertClass = array('message' => 'alert-success', 'notice' => 'alert-info', 'warning' => 'alert-warning', 'error' => 'alert-error');
?>
<div id="system-message-container">
	<?php if (is_array($msgList) && $msgList) : ?>
		<?php foreach ($msgList as $groupIdentifier => $msgs) : ?>
			<?php $options = unserialize($groupIdentifier); ?>
			<?php $type    = $options['type']; ?>
			<div class="alert <?php echo isset($alertClass[$type]) ? $alertClass[$type] : 'alert-' . $type; ?>">
				<button type="button" class="close" data-dismiss="alert">&times;</button>
				<?php if ($msgs) : ?>
					<?php if ($options['showTitle']) : ?>
						<?php if ($options['customTitle'] !== '') : ?>
							<?php $title = $options['customTitle']; ?>
						<?php else : ?>
							<?php $title = $type; ?>
						<?php endif; ?>
						<h4 class="alert-heading"><?php echo JText::_($title); ?></h4>
					<?php endif; ?>
					<?php foreach ($msgs as $msg) : ?>
						<div class="alert-message"><?php echo $msg; ?></div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>
