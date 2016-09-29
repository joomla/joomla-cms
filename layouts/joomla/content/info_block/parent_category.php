<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

?>
			<dd class="parent-category-name">
				<?php $title = $this->escape($displayData['item']->parent_title); ?>
				<?php if (!empty($displayData['item']->parent_slug) && $displayData['params']->get('link_parent_category')) : ?>
					<?php $url = '<a href="' . JRoute::_(ContentHelperRoute::getCategoryRoute($displayData['item']->parent_slug)) . '" itemprop="genre">' . $title . '</a>'; ?>
					<?php echo JText::sprintf('COM_CONTENT_PARENT', $url); ?>
				<?php else : ?>
					<?php echo JText::sprintf('COM_CONTENT_PARENT', '<span itemprop="genre">' . $title . '</span>'); ?>
				<?php endif; ?>
			</dd>