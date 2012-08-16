<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
?>

<h4 class="modal-title"><?php echo JText::_('COM_MODULES_TYPE_CHOOSE')?></h4>
<ul id="new-modules-list" class="list list-striped">
<?php foreach ($this->items as &$item) : ?>
	<?php
		// Prepare variables for the link.

		$link	= 'index.php?option=com_modules&task=module.add&eid='. $item->extension_id;
		$name	= $this->escape($item->name);
		$desc	= JHTML::_('string.truncate', ($this->escape($item->desc)), 200);
		$short_desc	= JHTML::_('string.truncate', ($this->escape($item->desc)), 90);
	?>
	<li rel="popover" data-placement="top" title="<?php echo $name; ?>" data-content="<?php echo $desc; ?>">
		<a href="<?php echo JRoute::_($link);?>">
			<h4><?php echo $name; ?> <small><?php echo $short_desc; ?></small></h4>
		</a>
	</li>
<?php endforeach; ?>
</ul>
<div class="clr"></div>
