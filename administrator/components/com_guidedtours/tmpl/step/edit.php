<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtours
 * @copyright (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\View\GenericDataException;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$app = Factory::getApplication();
$tour_id = $app->getUserState('com_guidedtours.tour_id');

if (empty($tour_id))
{
	throw new GenericDataException("\nThe Tour id was not set!\n", 500);
}

// Fieldsets to not automatically render by /layouts/joomla/edit/params.php
$this->ignore_fieldsets = array('details', 'item_associations', 'jmetadata');
$this->useCoreUI = true;
?>

<form action="<?php echo Route::_('index.php?option=com_guidedtours&view=step&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="guidedtour-dates-form" class="form-validate">

	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>
	<div>
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('Details')); ?>
		<div class="row">
			<div class="col-md-9">

				<?php echo $this->form->renderField('description'); ?>
				<?php echo $this->form->renderField('step-no'); ?>
				<?php echo $this->form->renderField('position'); ?>
				<?php echo $this->form->renderField('target'); ?>
				<?php echo $this->form->renderField('url'); ?>
				<?php $this->form->setValue('tour_id', null, $tour_id); ?>
				<?php echo $this->form->renderField('tour_id'); ?>

			</div>

			<div class="col-lg-3">
				<?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing', Text::_('JGLOBAL_FIELDSET_PUBLISHING')); ?>
		<div class="row">
			<div class="col-12 col-lg-8">
				<fieldset id="fieldset-publishingdata" class="options-form">
					<legend><?php echo Text::_('JGLOBAL_FIELDSET_PUBLISHING'); ?></legend>
					<div>
						<?php echo LayoutHelper::render('joomla.edit.publishingdata', $this); ?>
					</div>
				</fieldset>
			</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', Text::_('Permissions')); ?>
			<div class="row">
				<div class="col-12 col-lg">
					<fieldset id="fieldset-rules" class="options-form">
						<legend><?php echo Text::_('Permissions'); ?></legend>
						<?php echo $this->form->getInput('rules'); ?>
					</fieldset>
				</div>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>
			</div>

		</div>
		<input type="hidden" name="task" value="">
		<input type="hidden" name="tour_id" value="<?php echo $tour_id; ?>">
		<?php echo HTMLHelper::_('form.token'); ?>
</form>
