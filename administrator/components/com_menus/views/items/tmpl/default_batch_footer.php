<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
$published = $this->state->get('filter.published');
$clientId  = $this->state->get('filter.client_id');
$menuType = JFactory::getApplication()->getUserState('com_menus.items.menutype');
?>
<button type="button" class="btn" onclick="document.getElementById('batch-menu-id').value='';document.getElementById('batch-access').value='';document.getElementById('batch-language-id').value=''" data-dismiss="modal">
	<?php echo JText::_('JCANCEL'); ?>
</button>
<?php if ((strlen($menuType) && $menuType != '*' && $clientId == 0) || ($published > 0 && $clientId == 1)): ?>
	<button type="submit" class="btn btn-success" onclick="Joomla.submitbutton('item.batch');">
		<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
	</button>
<?php endif; ?>
