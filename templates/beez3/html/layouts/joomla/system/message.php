<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.beez3
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$msgList = $displayData['msgQueue'];
?>
<div id="system-message-container">
	<?php if (is_array($msgList) && $msgList) : ?>
		<dl id="system-message">
			<?php foreach ($msgList as $groupIdentifier => $msgs) : ?>
				<?php $options = unserialize($groupIdentifier); ?>
				<?php $type    = $options['type']; ?>
				<?php if ($msgs) : ?>
					<?php if ($options['showTitle']) : ?>
						<?php if ($options['customTitle'] !== '') : ?>
							<?php $title = $options['customTitle']; ?>
						<?php else : ?>
							<?php $title = $type; ?>
						<?php endif; ?>
						<dt class="<?php echo strtolower($type); ?>"><?php echo JText::_($title); ?></dt>
					<?php else : ?>
						<dt class="<?php echo strtolower($type); ?>">&nbsp;</dt>
					<?php endif; ?>
					<dt class="<?php echo strtolower($type); ?>"><?php echo JText::_($type); ?></dt>
					<dd class="<?php echo strtolower($type); ?> message">
						<ul>
							<?php foreach ($msgs as $msg) : ?>
								<li><?php echo $msg; ?></li>
							<?php endforeach; ?>
						</ul>
					</dd>
				<?php endif; ?>
			<?php endforeach; ?>
		</dl>
	<?php endif; ?>
</div>
