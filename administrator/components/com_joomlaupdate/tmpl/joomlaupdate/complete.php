<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

?>

<fieldset class="options-form">
	<legend>
		<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_COMPLETE_HEADING'); ?>
	</legend>
	<div>
		<div class="alert alert-success">
			<span class="fas fa-check-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('NOTICE'); ?></span>
			<?php echo Text::sprintf('COM_JOOMLAUPDATE_VIEW_COMPLETE_MESSAGE', '&#x200E;' . JVERSION); ?>
		</div>
	</div>
</fieldset>
<form action="<?php echo Route::_('index.php?option=com_joomlaupdate'); ?>" method="post" id="adminForm">
	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
