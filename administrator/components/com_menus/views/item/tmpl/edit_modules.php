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
<?php if (!empty($this->modules)) : ?>
	<input class="modal" type="button" rel="{handler:'clone', target:'menu-types'}" value="Change"/>
	<div class="clr"></div>
	<ul id="paramlist">
		<?php foreach ($this->modules as $i => &$module) : ?>
			<li><?php echo JText::sprintf('Menus_Item_Module_Access_Position', $this->escape($module->title), $this->escape($module->access_title), $this->escape($module->position)); ?></li>
		<?php endforeach; ?>
		</ul>
<?php endif; ?>
