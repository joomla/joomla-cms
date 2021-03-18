<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

HTMLHelper::_('script', 'com_contenthistory/admin-history-versions.js', ['version' => 'auto', 'relative' => true]);
?>
<form action="<?php echo Route::_('index.php?option=com_users&view=note&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="note-form" class="form-validate">
	<fieldset class="adminform">
	<div class="card mt-4">
		<div class="card-body">
			<div class="row">
				<div class="col-lg-8 col-xl-6">
				<?php echo $this->form->renderField('subject'); ?>
				<?php echo $this->form->renderField('user_id'); ?>
				<?php echo $this->form->renderField('catid'); ?>
				<?php echo $this->form->renderField('state'); ?>
				<?php echo $this->form->renderField('review_time'); ?>
				<?php echo $this->form->renderField('version_note'); ?>


				<input type="hidden" name="task" value="">
				<?php echo HTMLHelper::_('form.token'); ?>
				</div>
				<div class="col-12">
					<?php echo $this->form->renderField('body'); ?>
				</div>
			</div>
		</div>
	</div>
	</fieldset>
</form>
