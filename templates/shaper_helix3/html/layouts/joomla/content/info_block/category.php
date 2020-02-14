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
<dd class="category-name">
	<i class="fa fa-folder-open-o"></i>
	<?php $title = $this->escape($displayData['item']->category_title); ?>
	<?php if ($displayData['params']->get('link_category') && $displayData['item']->catslug) : ?>
		<?php echo '<a href="' . JRoute::_(ContentHelperRoute::getCategoryRoute($displayData['item']->catslug)) . '" itemprop="genre" data-toggle="tooltip" title="' . JText::_('COM_CONTENT_CONTENT_TYPE_CATEGORY') . '">' . $title . '</a>'; ?>
	<?php else : ?>
		<?php echo '<span itemprop="genre" itemprop="genre" data-toggle="tooltip" title="' . JText::_('COM_CONTENT_CONTENT_TYPE_CATEGORY') . '">' . $title . '</span>'; ?>
	<?php endif; ?>
</dd>