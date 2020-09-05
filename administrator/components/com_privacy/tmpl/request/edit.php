<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\Privacy\Administrator\View\Request\HtmlView $this */

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

?>

<form action="<?php echo Route::_('index.php?option=com_privacy&view=request&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
	<div class="form-horizontal">
		<div class="card mt-3">
			<div class="card-body">
				<fieldset class="adminform">
					<?php echo $this->form->renderField('email'); ?>
					<?php echo $this->form->renderField('status'); ?>
					<?php echo $this->form->renderField('request_type'); ?>
				</fieldset>
			</div>
		</div>

		<input type="hidden" name="task" value="" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
