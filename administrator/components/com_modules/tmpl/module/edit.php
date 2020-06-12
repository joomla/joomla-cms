<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
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
HTMLHelper::_('behavior.combobox');
HTMLHelper::_('behavior.keepalive');

$hasContent = isset($this->item->xml->customContent);
$hasContentFieldName = 'content';

// For a later improvement
if ($hasContent)
{
	$hasContentFieldName = 'content';
}

// Get Params Fieldsets
$this->fieldsets = $this->form->getFieldsets('params');
$this->useCoreUI = true;

Text::script('JYES');
Text::script('JNO');
Text::script('JALL');
Text::script('JTRASHED');

$this->document->addScriptOptions('module-edit', ['itemId' => $this->item->id, 'state' => (int) $this->item->id == 0 ? 'Add' : 'Edit']);
HTMLHelper::_('script', 'com_modules/admin-module-edit.min.js', array('version' => 'auto', 'relative' => true));

$input = Factory::getApplication()->input;

// In case of modal
$isModal = $input->get('layout') === 'modal';
$layout  = $isModal ? 'modal' : 'edit';
$tmpl    = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';

?>

<form action="<?php echo Route::_('index.php?option=com_modules&layout=' . $layout . $tmpl . '&client_id=' . $this->form->getValue('client_id') . '&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="module-form" class="form-validate">

	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div>
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_MODULES_MODULE')); ?>

		<div class="row">
			<div class="col-lg-9">
				<div class="card">
					<div class="card-body">
						<?php if ($this->item->xml) : ?>
							<?php if ($this->item->xml->description) : ?>
								<h2>
									<?php
									if ($this->item->xml)
									{
										echo ($text = (string) $this->item->xml->name) ? Text::_($text) : $this->item->module;
									}
									else
									{
										echo Text::_('COM_MODULES_ERR_XML');
									}
									?>
								</h2>
								<div class="info-labels">
									<span class="badge badge-secondary">
										<?php echo $this->item->client_id == 0 ? Text::_('JSITE') : Text::_('JADMINISTRATOR'); ?>
									</span>
								</div>
								<div>
									<?php
									$this->fieldset    = 'description';
									$short_description = Text::_($this->item->xml->description);
									$this->fieldset    = 'description';
									$long_description  = LayoutHelper::render('joomla.edit.fieldset', $this);

									if (!$long_description)
									{
										$truncated = JHtmlString::truncate($short_description, 550, true, false);

										if (strlen($truncated) > 500)
										{
											$long_description  = $short_description;
											$short_description = JHtmlString::truncate($truncated, 250);

											if ($short_description == $long_description)
											{
												$long_description = '';
											}
										}
									}
									?>
									<p><?php echo $short_description; ?></p>
									<?php if ($long_description) : ?>
										<p class="readmore">
											<a href="#" onclick="document.querySelector('#tab-description').click();">
												<?php echo Text::_('JGLOBAL_SHOW_FULL_DESCRIPTION'); ?>
											</a>
										</p>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						<?php else : ?>
							<div class="alert alert-danger">
								<span class="fas fa-exclamation-triangle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('ERROR'); ?></span>
								<?php echo Text::_('COM_MODULES_ERR_XML'); ?>
							</div>
						<?php endif; ?>
						<?php
						if ($hasContent)
						{
							echo $this->form->getInput($hasContentFieldName);
						}
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
						'showtitle',
						'position',
						'published',
						'publish_up',
						'publish_down',
						'access',
						'ordering',
						'language',
						'note'
					);

					?>
					<?php if ($this->item->client_id == 0) : ?>
						<?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
					<?php else : ?>
						<?php echo LayoutHelper::render('joomla.edit.admin_modules', $this); ?>
					<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php if (isset($long_description) && $long_description != '') : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'description', Text::_('JGLOBAL_FIELDSET_DESCRIPTION')); ?>
				<div class="card">
					<div class="card-body">
						<?php echo $long_description; ?>
					</div>
				</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>

		<?php if ($this->item->client_id == 0) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'assignment', Text::_('COM_MODULES_MENU_ASSIGNMENT')); ?>
			<fieldset id="fieldset-assignment" class="options-form">
				<legend><?php echo Text::_('COM_MODULES_MENU_ASSIGNMENT'); ?></legend>
				<div>
				<?php echo $this->loadTemplate('assignment'); ?>
				</div>
			</fieldset>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>

		<?php
		$this->fieldsets        = array();
		$this->ignore_fieldsets = array('basic', 'description');
		echo LayoutHelper::render('joomla.edit.params', $this);
		?>

		<?php if ($this->canDo->get('core.admin')) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', Text::_('COM_MODULES_FIELDSET_RULES')); ?>
			<fieldset id="fieldset-permissions" class="options-form">
				<legend><?php echo Text::_('COM_MODULES_FIELDSET_RULES'); ?></legend>
				<div>
				<?php echo $this->form->getInput('rules'); ?>
				</div>
			</fieldset>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

		<input type="hidden" name="task" value="">
		<input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>">
		<?php echo HTMLHelper::_('form.token'); ?>
		<?php echo $this->form->getInput('module'); ?>
		<?php echo $this->form->getInput('client_id'); ?>
	</div>
</form>
