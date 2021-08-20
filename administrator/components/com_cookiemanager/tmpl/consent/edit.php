<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cookiemanager
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

?>

<form action="<?php echo Route::_('index.php?option=com_cookiemanager&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="consent-form" aria-label="<?php echo Text::_('COM_COOKIEMANAGER_FORM_TITLE_REVIEW_CONSENT'); ?>" class="form-validate">
	<div class="main-card">
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_COOKIEMANAGER_CONSENTS')); ?>
		<div class="row">
			<div class="col-lg-9">
				<?php echo $this->form->renderField('uuid'); ?>
				<?php echo $this->form->renderField('ccuuid'); ?>
				<?php echo $this->form->renderField('consent_opt_in'); ?>
				<?php echo $this->form->renderField('consent_opt_out'); ?>
				<?php echo $this->form->renderField('consent_date'); ?>
				<?php echo $this->form->renderField('url'); ?>
				<?php echo $this->form->renderField('user_agent'); ?>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
	</div>

	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
