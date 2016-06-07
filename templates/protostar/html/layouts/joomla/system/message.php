<?php
/**
 * @package     Joomla.Site
 * @subpackage  Template.protostar
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$msgList    = $displayData['msgQueue'];
$alertClass = array('notice' => 'alert-info', 'message' => 'alert-success');
?>
<div id="system-message-container">
	<?php if (is_array($msgList) && !empty($msgList)) : ?>
		<div id="system-message">
			<?php foreach ($msgList as $groupIdentifier => $msgs) : ?>
				<?php $options = unserialize($groupIdentifier); ?>
				<?php $type    = $options['type']; ?>
				<div class="alert <?php echo isset($alertClass[$type]) ? $alertClass[$type] : 'alert-' . $type; ?>">
					<?php // This requires JS so we should add it trough JS. Progressive enhancement and stuff. ?>
					<a class="close" data-dismiss="alert">×</a>

					<?php if (!empty($msgs)) : ?>
						<?php if ($options['showTitle']) : ?>
							<?php if ($options['customTitle'] !== '') : ?>
								<?php $title = $options['customTitle']; ?>
							<?php else : ?>
								<?php $title = $type; ?>
							<?php endif; ?>
							<h4 class="alert-heading"><?php echo JText::_($title); ?></h4>
						<?php endif; ?>
						<div>
							<?php foreach ($msgs as $msg) : ?>
								<div class="alert-message"><?php echo $msg; ?></div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
