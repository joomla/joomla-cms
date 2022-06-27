<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

?>
<div class="card">
	<h2 class="card-header"><?php echo Text::_('COM_JOOMLAUPDATE_VIEW_COMPLETE_HEADING'); ?></h2>
	<div class="card-body">
		<div class="alert alert-success">
			<span class="icon-check-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('NOTICE'); ?></span>
			<?php echo Text::sprintf('COM_JOOMLAUPDATE_VIEW_COMPLETE_MESSAGE', '&#x200E;' . JVERSION); ?>
		</div>
	</div>
</div>

<form action="<?php echo Route::_('index.php?option=com_joomlaupdate'); ?>" method="post" id="adminForm">
	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
