<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>
<button type="button" class="btn btn-secondary" onclick="document.getElementById('batch-group-id').value=''" data-bs-dismiss="modal">
	<?php echo Text::_('JCANCEL'); ?>
</button>
<button type="submit" class="btn btn-success" onclick="Joomla.submitbutton('user.batch');return false;">
	<?php echo Text::_('JGLOBAL_BATCH_PROCESS'); ?>
</button>
