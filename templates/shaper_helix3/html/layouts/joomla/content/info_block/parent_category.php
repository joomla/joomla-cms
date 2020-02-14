<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

?>
<dd class="parent-category-name">
	<i class="fa fa-folder-o"></i>
	<?php $title = $this->escape($displayData['item']->parent_title); ?>
	<?php if ($displayData['params']->get('link_parent_category') && !empty($displayData['item']->parent_slug)) : ?>
		<?php echo '<a href="' . JRoute::_(ContentHelperRoute::getCategoryRoute($displayData['item']->parent_slug)) . '" itemprop="genre" data-toggle="tooltip" title="' . JText::sprintf('COM_CONTENT_PARENT', '') . '">' . $title . '</a>'; ?>
	<?php else : ?>
		<?php echo '<span itemprop="genre" data-toggle="tooltip" title="' . JText::sprintf('COM_CONTENT_PARENT', '') . '">' . $title . '</span>'; ?>
	<?php endif; ?>
</dd>