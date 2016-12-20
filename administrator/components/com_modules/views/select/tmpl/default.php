<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.popover');
$document = JFactory::getDocument();
?>

<h2><?php echo JText::_('COM_MODULES_TYPE_CHOOSE'); ?></h2>
<ul id="new-modules-list" class="list list-striped">
<?php foreach ($this->items as &$item) : ?>
	<?php // Prepare variables for the link. ?>
	<?php $link       = 'index.php?option=com_modules&task=module.add&eid=' . $item->extension_id; ?>
	<?php $name       = $this->escape($item->name); ?>
	<?php $desc       = JHtml::_('string.truncate', ($this->escape(strip_tags($item->desc))), 200); ?>
	<?php $short_desc = JHtml::_('string.truncate', ($this->escape(strip_tags($item->desc))), 90); ?>

	<?php if ($document->direction != "rtl") : ?>
	<li>
		<a href="<?php echo JRoute::_($link); ?>">
			<strong><?php echo $name; ?></strong></a>
		<small class="hasPopover" data-placement="right" title="<?php echo $name; ?>" data-content="<?php echo $desc; ?>"><?php echo $short_desc; ?></small>
	</li>
	<?php else : ?>
	<li>
		<small rel="popover" data-placement="left" title="<?php echo $name; ?>" data-content="<?php echo $desc; ?>"><?php echo $short_desc; ?></small>
		<a href="<?php echo JRoute::_($link); ?>">
			<strong><?php echo $name; ?></strong></a>
	</li>
	<?php endif; ?>
<?php endforeach; ?>
</ul>
<div class="clr"></div>
