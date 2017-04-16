<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.combobox');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tabstate');
JHtml::_('formbehavior.chosen', '#jform_position', null, array('disable_search_threshold' => 0 ));

$hasContent = empty($this->item->module) ||  isset($this->item->xml->customContent);
$hasContentFieldName = 'content';

// For a later improvement
if ($hasContent)
{
	$hasContentFieldName = 'content';
}

// Get Params Fieldsets
$this->fieldsets = $this->form->getFieldsets('params');

JText::script('JYES');
JText::script('JNO');
JText::script('JALL');
JText::script('JTRASHED');

JFactory::getDocument()->addScriptOptions('module-edit', ['itemId' => $this->item->id, 'state' => (int) $this->item->id == 0 ? 'Add' : 'Edit']);
JHtml::_('script', 'com_modules/admin-module-edit.min.js', array('version' => 'auto', 'relative' => true));

$input = JFactory::getApplication()->input;

// In case of modal
$isModal = $input->get('layout') == 'modal' ? true : false;
$layout  = $isModal ? 'modal' : 'edit';
$tmpl    = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';
?>

<form action="<?php echo JRoute::_('index.php?option=com_modules&layout=' . $layout . $tmpl . '&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="module-form" class="form-validate">

	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div>
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_MODULES_MODULE')); ?>

		<div class="row">
			<div class="col-md-9">
				<?php if ($this->item->xml) : ?>
					<?php if ($this->item->xml->description) : ?>
						<h3>
							<?php
							if ($this->item->xml)
							{
								echo ($text = (string) $this->item->xml->name) ? JText::_($text) : $this->item->module;
							}
							else
							{
								echo JText::_('COM_MODULES_ERR_XML');
							}
							?>
						</h3>
						<div class="info-labels">
							<span class="badge badge-default hasTooltip" title="<?php echo JHtml::_('tooltipText', 'COM_MODULES_FIELD_CLIENT_ID_LABEL'); ?>">
								<?php echo $this->item->client_id == 0 ? JText::_('JSITE') : JText::_('JADMINISTRATOR'); ?>
							</span>
						</div>
						<div>
							<?php
							$this->fieldset    = 'description';
							$short_description = JText::_($this->item->xml->description);
							$this->fieldset    = 'description';
							$long_description  = JLayoutHelper::render('joomla.edit.fieldset', $this);

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
									<a href="#" onclick="jQuery('.nav-tabs a[href=\'#description\']').tab('show');">
										<?php echo JText::_('JGLOBAL_SHOW_FULL_DESCRIPTION'); ?>
									</a>
								</p>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				<?php else : ?>
					<div class="alert alert-danger"><?php echo JText::_('COM_MODULES_ERR_XML'); ?></div>
				<?php endif; ?>
				<?php
				if ($hasContent)
				{
					echo $this->form->getInput($hasContentFieldName);
				}
				$this->fieldset = 'basic';
				$html = JLayoutHelper::render('joomla.edit.fieldset', $this);
				echo $html ? '<hr>' . $html : '';
				?>
			</div>
			<div class="col-md-3">
				<div class="card card-block card-light">
					<fieldset class="form-vertical form-no-margin">
						<?php echo $this->form->renderField('showtitle'); ?>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('position'); ?>
							</div>
							<div class="controls">
								<?php echo $this->loadTemplate('positions'); ?>
							</div>
						</div>
					</fieldset>
					<?php
					// Set main fields.
					$this->fields = array(
						'published',
						'publish_up',
						'publish_down',
						'access',
						'ordering',
						'language',
						'note'
					);

					?>
					<?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
				</div>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php if (isset($long_description) && $long_description != '') : ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'description', JText::_('JGLOBAL_FIELDSET_DESCRIPTION')); ?>
			<?php echo $long_description; ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif; ?>

		<?php if ($this->item->client_id == 0) : ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'assignment', JText::_('COM_MODULES_MENU_ASSIGNMENT')); ?>
			<?php echo $this->loadTemplate('assignment'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif; ?>

		<?php
		$this->fieldsets        = array();
		$this->ignore_fieldsets = array('basic', 'description');
		echo JLayoutHelper::render('joomla.edit.params', $this);
		?>

		<?php if ($this->canDo->get('core.admin')) : ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'permissions', JText::_('COM_MODULES_FIELDSET_RULES')); ?>
			<?php echo $this->form->getInput('rules'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif; ?>

		<?php echo JHtml::_('bootstrap.endTabSet'); ?>

		<input type="hidden" name="task" value="">
		<?php echo JHtml::_('form.token'); ?>
		<?php echo $this->form->getInput('module'); ?>
		<?php echo $this->form->getInput('client_id'); ?>
	</div>
</form>
