<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @deprecated  3.2
 */

defined('_JEXEC') or die;

$app = JFactory::getApplication();

// JLayout for standard handling of the details sidebar in administrator edit screens.
$title = $displayData->getForm()->getValue('title');
$published = $displayData->getForm()->getField('published');
$saveHistory = $displayData->get('state')->get('params')->get('save_history', 0);
?>
<div class="span2">
	<h4><?php echo JText::_('JDETAILS'); ?></h4>
	<hr />
	<fieldset class="form-vertical">
		<?php if (empty($title)) : ?>
			<div class="control-group">
				<div class="controls">
					<?php echo $displayData->getForm()->getValue('name'); ?>
				</div>
			</div>
		<?php else : ?>
			<div class="control-group">
				<div class="controls">
					<?php echo $displayData->getForm()->getValue('title'); ?>
				</div>
			</div>
		<?php endif; ?>
		<?php if ($published) : ?>
			<div class="control-group">
				<div class="control-label">
					<?php echo $displayData->getForm()->getLabel('published'); ?>
				</div>
				<div class="controls">
					<?php echo $displayData->getForm()->getInput('published'); ?>
				</div>
			</div>
		<?php else : ?>
			<div class="control-group">
				<div class="control-label">
					<?php echo $displayData->getForm()->getLabel('state'); ?>
				</div>
				<div class="controls">
					<?php echo $displayData->getForm()->getInput('state'); ?>
				</div>
			</div>
		<?php endif; ?>
		<div class="control-group">
			<div class="control-label">
				<?php echo $displayData->getForm()->getLabel('access'); ?>
			</div>
			<div class="controls">
				<?php echo $displayData->getForm()->getInput('access'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $displayData->getForm()->getLabel('featured'); ?>
			</div>
			<div class="controls">
				<?php echo $displayData->getForm()->getInput('featured'); ?>
			</div>
		</div>
		<?php if (JLanguageMultilang::isEnabled()) : ?>
			<div class="control-group">
				<div class="control-label">
					<?php echo $displayData->getForm()->getLabel('language'); ?>
				</div>
				<div class="controls">
					<?php echo $displayData->getForm()->getInput('language'); ?>
				</div>
			</div>
		<?php else : ?>
		<input type="hidden" id="jform_language" name="jform[language]" value="<?php echo $displayData->getForm()->getValue('language'); ?>" />
		<?php endif; ?>
		<div class="control-group">
			<div class="control-label">
				<?php echo $displayData->getForm()->getLabel('tags'); ?>
			</div>
			<div class="controls">
				<?php echo $displayData->getForm()->getInput('tags'); ?>
			</div>
		</div>
		<?php if ($saveHistory) : ?>
			<div class="control-group">
			<div class="control-label">
				<?php echo $displayData->getForm()->getLabel('version_note'); ?>
			</div>
			<div class="controls">
				<?php echo $displayData->getForm()->getInput('version_note'); ?>
			</div>
			</div>
		<?php endif; ?>
	</fieldset>
</div>
