<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');


$expired = ($this->state->get('cache_expired') == 1 ) ? '1' : '';

JHtml::_('stylesheet', 'com_languages/overrider.css', array('version' => 'auto', 'relative' => true));

JHtml::_('behavior.core');
JHtml::_('jquery.framework');
JHtml::_('script', 'com_languages/overrider.min.js', array('version' => 'auto', 'relative' => true));

JFactory::getDocument()->addScriptDeclaration('
	jQuery(document).ready(function($) {
		$("#jform_searchstring").on("focus", function() {
			if (!Joomla.overrider.states.refreshed)
			{
				var expired = "' . $expired . '";
				if (expired)
				{
					Joomla.overrider.refreshCache();
					Joomla.overrider.states.refreshed = true;
				}
			}
			$(this).removeClass("invalid");
		});
	});
');
?>

<form action="<?php echo JRoute::_('index.php?option=com_languages&id=' . $this->item->key); ?>" method="post" name="adminForm" id="override-form" class="form-validate">
	<div class="row">
		<div class="col-md-6">
			<fieldset>
				<legend><?php echo empty($this->item->key) ? JText::_('COM_LANGUAGES_VIEW_OVERRIDE_EDIT_NEW_OVERRIDE_LEGEND') : JText::_('COM_LANGUAGES_VIEW_OVERRIDE_EDIT_EDIT_OVERRIDE_LEGEND'); ?></legend>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('language'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('language'); ?>
					</div>
				</div>

				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('client'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('client'); ?>
					</div>
				</div>

				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('key'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('key'); ?>
					</div>
				</div>

				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('override'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('override'); ?>
					</div>
				</div>

				<?php if ($this->state->get('filter.client') == 'administrator') : ?>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('both'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('both'); ?>
					</div>
				</div>
				<?php endif; ?>

				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('file'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('file'); ?>
					</div>
				</div>
			</fieldset>

		</div>

		<div class="col-md-6">
			<fieldset>
				<legend><?php echo JText::_('COM_LANGUAGES_VIEW_OVERRIDE_SEARCH_LEGEND'); ?></legend>

				<joomla-alert type="info"><?php echo JText::_('COM_LANGUAGES_VIEW_OVERRIDE_SEARCH_TIP'); ?></joomla-alert>

				<div class="control-group">
					<div class="input-group">
						<?php echo $this->form->getInput('searchstring'); ?>
						<span class="input-group-btn">
							<button type="submit" class="btn btn-primary" onclick="Joomla.overrider.searchStrings();return false;" formnovalidate>
								<?php echo JText::_('COM_LANGUAGES_VIEW_OVERRIDE_SEARCH_BUTTON'); ?>
							</button>
						</span>
					</div>
					<span id="refresh-status" class="help-block">
						<span class="fa fa-refresh fa-spin" aria-hidden="true"></span>
						<?php echo JText::_('COM_LANGUAGES_VIEW_OVERRIDE_REFRESHING'); ?>
					</span>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('searchtype'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('searchtype'); ?>
					</div>
				</div>

			</fieldset>

			<fieldset id="results-container" class="adminform">
				<legend><?php echo JText::_('COM_LANGUAGES_VIEW_OVERRIDE_RESULTS_LEGEND'); ?></legend>
				<div id="overrider-spinner" class="overrider-spinner text-center"><span class="fa fa-spinner fa-spin" aria-hidden="true"></span></div>
				<span id="more-results" class="mt-2">
					<a href="javascript:Joomla.overrider.searchStrings(Joomla.overrider.states.more);" class="btn btn-secondary">
						<span id="overrider-spinner-btn" class="overrider-spinner-btn fa fa-spinner fa-spin" aria-hidden="true"></span>
						<?php echo JText::_('COM_LANGUAGES_VIEW_OVERRIDE_MORE_RESULTS'); ?></a>
				</span>
			</fieldset>

			<input type="hidden" name="task" value="">
			<input type="hidden" name="id" value="<?php echo $this->item->key; ?>">

			<?php echo JHtml::_('form.token'); ?>
		</div>
	</div>
</form>
