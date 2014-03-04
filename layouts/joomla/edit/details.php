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
			<?php echo $displayData->getForm()->getControlGroup('published'); ?>
		<?php else : ?>
			<?php echo $displayData->getForm()->getControlGroup('state'); ?>
		<?php endif; ?>
		<?php echo $displayData->getForm()->getControlGroup('access'); ?>
		<?php echo $displayData->getForm()->getControlGroup('featured'); ?>
		<?php if (JLanguageMultilang::isEnabled()) : ?>
			<?php echo $displayData->getForm()->getControlGroup('language'); ?>
		<?php else : ?>
		<input type="hidden" name="language" value="<?php echo $displayData->getForm()->getValue('language'); ?>" />
		<?php endif; ?>
		<?php echo $displayData->getForm()->getControlGroup('tags'); ?>
		<?php if ($saveHistory) : ?>
			<?php echo $displayData->getForm()->getControlGroup('version_note'); ?>
		<?php endif; ?>
	</fieldset>
</div>
