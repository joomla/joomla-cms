<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$this->useCoreUI = true;

$user = Factory::getUser();
?>

<form action="<?php echo Route::_('index.php?option=com_templates&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="style-form" class="form-validate">

	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div>
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('JDETAILS')); ?>

		<div class="row">
			<div class="col-lg-9">
				<div class="card">
					<h2 class="card-header">
						<?php echo Text::_($this->item->template); ?>
					</h2>
					<div class="card-body">
						<div class="info-labels">
							<span class="badge badge-secondary">
								<?php echo $this->item->client_id == 0 ? Text::_('JSITE') : Text::_('JADMINISTRATOR'); ?>
							</span>
						</div>
						<div>
							<p><?php echo Text::_($this->item->xml->description); ?></p>
							<?php
							$this->fieldset = 'description';
							$description = LayoutHelper::render('joomla.edit.fieldset', $this);
							?>
							<?php if ($description) : ?>
								<p class="readmore">
									<a href="#" onclick="document.querySelector('#tab-description').click();">
										<?php echo Text::_('JGLOBAL_SHOW_FULL_DESCRIPTION'); ?>
									</a>
								</p>
							<?php endif; ?>
						</div>
						<?php
						$this->fieldset = 'basic';
						$html = LayoutHelper::render('joomla.edit.fieldset', $this);
						echo $html ? '<hr>' . $html : '';
						?>
					</div>
				</div>
			</div>
			<div class="col-lg-3">
				<div class="card">
					<div class="card-body">
						<?php
						// Set main fields.
						$this->fields = array(
							'home',
							'client_id',
							'template'
						);
						?>
						<?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
					</div>
				</div>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php if ($description) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'description', Text::_('JGLOBAL_FIELDSET_DESCRIPTION')); ?>
			<fieldset id="fieldset-description" class="options-form">
				<legend><?php echo Text::_('JGLOBAL_FIELDSET_DESCRIPTION'); ?></legend>
				<div>
				<?php echo $description; ?>
				</div>
			</fieldset>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>

		<?php
		$this->fieldsets = array();
		$this->ignore_fieldsets = array('basic', 'description');
		echo LayoutHelper::render('joomla.edit.params', $this);
		?>

		<?php if ($user->authorise('core.edit', 'com_menus') && $this->item->client_id == 0 && $this->canDo->get('core.edit.state')) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'assignment', Text::_('COM_TEMPLATES_MENUS_ASSIGNMENT')); ?>
			<fieldset id="fieldset-assignment" class="options-form">
				<legend><?php echo Text::_('COM_TEMPLATES_MENUS_ASSIGNMENT'); ?></legend>
				<div>
				<?php echo $this->loadTemplate('assignment'); ?>
				</div>
			</fieldset>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
