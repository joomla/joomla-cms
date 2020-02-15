<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$params = $displayData['params'];
$item = $displayData['item'];
$direction = JFactory::getLanguage()->isRtl() ? 'left' : 'right';
?>

<p class="readmore">
	<?php if (!$params->get('access-view')) : ?>
		<a class="btn" href="<?php echo $displayData['link']; ?>" itemprop="url" aria-label="<?php echo JText::_('COM_CONTENT_REGISTER_TO_READ_MORE'); ?> 
			<?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>">
			<?php echo '<span class="icon-chevron-' . $direction . '" aria-hidden="true"></span>'; ?>
			<?php echo JText::_('COM_CONTENT_REGISTER_TO_READ_MORE'); ?>
		</a>
	<?php elseif ($readmore = $item->alternative_readmore) : ?>
		<a class="btn" href="<?php echo $displayData['link']; ?>" itemprop="url" aria-label="<?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>">
			<?php echo '<span class="icon-chevron-' . $direction . '" aria-hidden="true"></span>'; ?> 
			<?php echo $readmore; ?>
			<?php if ($params->get('show_readmore_title', 0) != 0) : ?>
				<?php echo JHtml::_('string.truncate', $item->title, $params->get('readmore_limit')); ?>
			<?php endif; ?>
		</a>
	<?php elseif ($params->get('show_readmore_title', 0) == 0) : ?>
		<a class="btn" href="<?php echo $displayData['link']; ?>" itemprop="url" aria-label="<?php echo JText::_('COM_CONTENT_READ_MORE'); ?> <?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>">
			<?php echo '<span class="icon-chevron-' . $direction . '" aria-hidden="true"></span>'; ?> 
			<?php echo JText::sprintf('COM_CONTENT_READ_MORE_TITLE'); ?>
		</a>
	<?php else : ?>
		<a class="btn" href="<?php echo $displayData['link']; ?>" itemprop="url" aria-label="<?php echo JText::_('COM_CONTENT_READ_MORE'); ?> <?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>">
			<?php echo '<span class="icon-chevron-' . $direction . '" aria-hidden="true"></span>'; ?> 
			<?php echo JText::_('COM_CONTENT_READ_MORE'); ?>
			<?php echo JHtml::_('string.truncate', $item->title, $params->get('readmore_limit')); ?>
		</a>
	<?php endif; ?>
</p>
