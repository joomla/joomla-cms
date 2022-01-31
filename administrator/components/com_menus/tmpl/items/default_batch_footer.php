<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

$published = $this->state->get('filter.published');
$clientId  = $this->state->get('filter.client_id');
$menuType = Factory::getApplication()->getUserState('com_menus.items.menutype', '');
?>
<button type="button" class="btn btn-secondary" onclick="document.getElementById('batch-menu-id').value='';document.getElementById('batch-access').value='';document.getElementById('batch-language-id').value=''" data-bs-dismiss="modal">
	<?php echo Text::_('JCANCEL'); ?>
</button>
<?php if ((strlen($menuType) && $menuType != '*' && $clientId == 0) || ($published >= 0 && $clientId == 1)): ?>
	<button type="submit" class="btn btn-success" onclick="Joomla.submitbutton('item.batch');return false;">
		<?php echo Text::_('JGLOBAL_BATCH_PROCESS'); ?>
	</button>
<?php endif; ?>
