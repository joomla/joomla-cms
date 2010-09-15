<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_modules
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
?>

<?php foreach ($this->groups as $clientId => &$group) : ?>
	<?php if (!empty($group)) : ?>
	<h3>
		<?php echo $clientId == 0 ? JText::_('JSITE') : JText::_('JADMINISTRATOR'); ?>
	</h3>

	<ul id="new-modules-list">
	<?php foreach ($group as &$item) : ?>
		<li>
			<?php
			// Prepare variables for the link.
			$link	= 'index.php?option=com_modules&task=module.add&eid='. $item->extension_id;
			$name	= $this->escape(JText::_($item->name));
			$desc	= $this->escape(JText::_($item->description));
			?>
			<span class="editlinktip hasTip" title="<?php echo $name.' :: '.$desc; ?>">
				<a href="<?php echo JRoute::_($link);?>" target="_top">
					<?php echo $name; ?></a></span>
		</li>
	<?php endforeach; ?>
	</ul>
	<?php endif; ?>
<?php endforeach; ?>
