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
<dd class="create">
	<i class="fa fa-clock-o"></i>
	<time datetime="<?php echo JHtml::_('date', $displayData['item']->created, 'c'); ?>" itemprop="dateCreated" data-toggle="tooltip" title="<?php echo JText::_('COM_CONTENT_CREATED_DATE'); ?>">
		<?php echo JHtml::_('date', $displayData['item']->created, JText::_('DATE_FORMAT_LC3')); ?>
	</time>
</dd>