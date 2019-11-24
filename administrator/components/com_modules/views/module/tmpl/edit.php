<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.combobox');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', '#jform_position', null, array('disable_search_threshold' => 0 ));
JHtml::_('formbehavior.chosen', '.multipleCategories', null, array('placeholder_text_multiple' => JText::_('JOPTION_SELECT_CATEGORY')));
JHtml::_('formbehavior.chosen', '.multipleTags', null, array('placeholder_text_multiple' => JText::_('JOPTION_SELECT_TAG')));
JHtml::_('formbehavior.chosen', '.multipleAuthors', null, array('placeholder_text_multiple' => JText::_('JOPTION_SELECT_AUTHOR')));
JHtml::_('formbehavior.chosen', '.multipleAuthorAliases', null, array('placeholder_text_multiple' => JText::_('JOPTION_SELECT_AUTHOR_ALIAS')));
JHtml::_('formbehavior.chosen', 'select');

$hasContent = isset($this->item->xml->customContent);
$hasContentFieldName = 'content';

// For a later improvement
if ($hasContent)
{
	$hasContentFieldName = 'content';
}

// Get Params Fieldsets
$this->fieldsets = $this->form->getFieldsets('params');

$script = "
	Joomla.submitbutton = function(task) {
			if (task == 'module.cancel' || document.formvalidator.isValid(document.getElementById('module-form')))
			{
";
if ($hasContent)
{
	$script .= $this->form->getField($hasContentFieldName)->save();
}
$script .= "
			Joomla.submitform(task, document.getElementById('module-form'));

				jQuery('#permissions-sliders select').attr('disabled', 'disabled');

				if (self != top)
				{
					if (parent.viewLevels)
					{
						var updPosition = jQuery('#jform_position').chosen().val(),
							updTitle = jQuery('#jform_title').val(),
							updMenus = jQuery('#jform_assignment').chosen().val(),
							updStatus = jQuery('#jform_published').chosen().val(),
							updAccess = jQuery('#jform_access').chosen().val(),
							tmpMenu = jQuery('#menus-" . $this->item->id . "', parent.document),
							tmpRow = jQuery('#tr-" . $this->item->id . "', parent.document);
							tmpStatus = jQuery('#status-" . $this->item->id . "', parent.document);
							window.parent.inMenus = new Array();
							window.parent.numMenus = jQuery(':input[name=\"jform[assigned][]\"]').length;

						jQuery('input[name=\"jform[assigned][]\"]').each(function(){
							if (updMenus > 0 )
							{
								if (jQuery(this).is(':checked'))
								{
									window.parent.inMenus.push(parseInt(jQuery(this).val()));
								}
							}
							if (updMenus < 0 )
							{
								if (!jQuery(this).is(':checked'))
								{
									window.parent.inMenus.push(parseInt(jQuery(this).val()));
								}
							}
						});
						if (updMenus == 0) {
							tmpMenu.html('<span class=\"label label-info\">" . JText::_('JALL') . "</span>');
							if (tmpRow.hasClass('no')) { tmpRow.removeClass('no '); }
						}
						if (updMenus == '-') {
							tmpMenu.html('<span class=\"label label-important\">" . JText::_('JNO') . "</span>');
							if (!tmpRow.hasClass('no') || tmpRow.hasClass('')) { tmpRow.addClass('no '); }
						}
						if (updMenus > 0) {
							if (window.parent.inMenus.indexOf(parent.menuId) >= 0)
							{
								if (window.parent.numMenus == window.parent.inMenus.length)
								{
									tmpMenu.html('<span class=\"label label-info\">" . JText::_('JALL') . "</span>');
									if (tmpRow.hasClass('no') || tmpRow.hasClass('')) { tmpRow.removeClass('no'); }
								}
								else
								{
									tmpMenu.html('<span class=\"label label-success\">" . JText::_('JYES') . "</span>');
									if (tmpRow.hasClass('no')) { tmpRow.removeClass('no'); }
								}
							}
							if (window.parent.inMenus.indexOf(parent.menuId) < 0)
							{
								tmpMenu.html('<span class=\"label label-important\">" . JText::_('JNO') . "</span>');
								if (!tmpRow.hasClass('no')) { tmpRow.addClass('no'); }
							}
						}
						if (updMenus < 0) {
							if (window.parent.inMenus.indexOf(parent.menuId) >= 0)
							{
								if (window.parent.numMenus == window.parent.inMenus.length)
								{
									tmpMenu.html('<span class=\"label label-info\">" . JText::_('JALL') . "</span>');
									if (tmpRow.hasClass('no')) { tmpRow.removeClass('no'); }
								}
								else
								{
									tmpMenu.html('<span class=\"label label-success\">" . JText::_('JYES') . "</span>');
									if (tmpRow.hasClass('no')) { tmpRow.removeClass('no'); }
								}
							}
							if (window.parent.inMenus.indexOf(parent.menuId) < 0)
							{
								tmpMenu.html('<span class=\"label label-important\">" . JText::_('JNO') . "</span>');
								if (!tmpRow.hasClass('no') || tmpRow.hasClass('')) { tmpRow.addClass('no'); }
							}
						}
						if (updStatus == 1) {
							tmpStatus.html('<span class=\"label label-success\">" . JText::_('JYES') . "</span>');
							if (tmpRow.hasClass('unpublished')) { tmpRow.removeClass('unpublished '); }
						}
						if (updStatus == 0) {
							tmpStatus.html('<span class=\"label label-important\">" . JText::_('JNO') . "</span>');
							if (!tmpRow.hasClass('unpublished') || tmpRow.hasClass('')) { tmpRow.addClass('unpublished'); }
						}
						if (updStatus == -2) {
							tmpStatus.html('<span class=\"label label-default\">" . JText::_('JTRASHED') . "</span>');
							if (!tmpRow.hasClass('unpublished') || tmpRow.hasClass('')) { tmpRow.addClass('unpublished'); }
						}
						if (document.formvalidator.isValid(document.getElementById('module-form'))) {
							jQuery('#title-" . $this->item->id . "', parent.document).text(updTitle);
							jQuery('#position-" . $this->item->id . "', parent.document).text(updPosition);
							jQuery('#access-" . $this->item->id . "', parent.document).html(parent.viewLevels[updAccess]);
						}
					}
				}

				if (task !== 'module.apply')
				{
					window.parent.jQuery('#module" . ((int) $this->item->id == 0 ? 'Add' : 'Edit' . (int) $this->item->id) . "Modal').modal('hide');
				}
			}
	};";

JFactory::getDocument()->addScriptDeclaration($script);

$input = JFactory::getApplication()->input;

// In case of modal
$isModal = $input->get('layout') == 'modal' ? true : false;
$layout  = $isModal ? 'modal' : 'edit';
$tmpl    = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';
?>

<form action="<?php echo JRoute::_('index.php?option=com_modules&layout=' . $layout . $tmpl . '&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="module-form" class="form-validate">

	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_MODULES_MODULE')); ?>

		<div class="row-fluid">
			<div class="span9">
				<?php if ($this->item->xml) : ?>
					<?php if ($this->item->xml->description) : ?>
						<h2>
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
						</h2>
						<div class="info-labels">
							<span class="label hasTooltip" title="<?php echo JHtml::_('tooltipText', 'COM_MODULES_FIELD_CLIENT_ID_LABEL'); ?>">
								<?php echo $this->item->client_id == 0 ? JText::_('JSITE') : JText::_('JADMINISTRATOR'); ?>
							</span>
						</div>
						<div>
							<?php
							$short_description = JText::_($this->item->xml->description);
							$this->fieldset = 'description';
							$long_description = JLayoutHelper::render('joomla.edit.fieldset', $this);
							if (!$long_description) {
								$truncated = JHtmlString::truncate($short_description, 550, true, false);
								if (strlen($truncated) > 500) {
									$long_description = $short_description;
									$short_description = JHtmlString::truncate($truncated, 250);
									if ($short_description == $long_description) {
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
					<div class="alert alert-error"><?php echo JText::_('COM_MODULES_ERR_XML'); ?></div>
				<?php endif; ?>
				<?php
				if ($hasContent)
				{
					echo $this->form->getInput($hasContentFieldName);
				}
				$this->fieldset = 'basic';
				$html = JLayoutHelper::render('joomla.edit.fieldset', $this);
				echo $html ? '<hr />' . $html : '';
				?>
			</div>
			<div class="span3">
				<fieldset class="form-vertical">
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
		$this->fieldsets = array();
		$this->ignore_fieldsets = array('basic', 'description');
		echo JLayoutHelper::render('joomla.edit.params', $this);
		?>

		<?php if ($this->canDo->get('core.admin')) : ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'permissions', JText::_('COM_MODULES_FIELDSET_RULES')); ?>
			<?php echo $this->form->getInput('rules'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif; ?>

		<?php echo JHtml::_('bootstrap.endTabSet'); ?>

		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
		<?php echo $this->form->getInput('module'); ?>
		<?php echo $this->form->getInput('client_id'); ?>
	</div>
</form>
