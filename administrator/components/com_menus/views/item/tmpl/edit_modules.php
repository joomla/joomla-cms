<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<p><?php echo JText::_('Menus_Item_Module_assignment_desc');?></p>

<!-- TO DO: Remove options and replace with single Change button for modal. -->

<?php if (!empty($this->modules)) : ?>
	<table class="adminlist">
		<thead>
		<tr>
			<th class="left">
				<?php echo JText::_('Menus_Heading_Assign_Module');?>
			</th>
			<th>
				<?php echo JText::_('Menus_Heading_Display');?>
			</th>
			<th>
				<?php echo JText::_('Menus_Heading_Edit_Link'); ?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($this->modules as $i => &$module) : ?>
				<tr class="row<?php echo $i % 2;?>">
					<td>
						<?php echo JText::sprintf('Menus_Item_Module_Access_Position', $this->escape($module->title), $this->escape($module->access_title), $this->escape($module->position)); ?>
					</td>
					<td class="center">
					<?php				if (is_null($module->menuid)) {
						echo JText::_('Menus_Module_Show_None');
					} else if ($module->menuid != 0) {
						echo JText::_('Menus_Module_Show_Varies');
					} else {
						echo JText::_('Menus_Module_Show_All');
					} ?>

					</td>
					<td class="center">
					<?php $document = &JFactory::getDocument();
						$document->addScriptDeclaration($js); ?>
						<?php
							$link = 'index.php?option=com_modules&client=0&task=edit&cid[]='. $module->id.'&tmpl=component' ;
						 	JHTML::_('behavior.modal', 'a.modal');
							$html = '    <a class="modal" title="'.JText::_('Edit').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 650, y: 375}}">'.JText::_('Edit_module_settings').'</a>';

							echo  $html;

							?>
					</td>
				</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>
