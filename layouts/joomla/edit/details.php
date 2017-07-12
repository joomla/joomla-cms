<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @deprecated  3.2
 */

defined('JPATH_BASE') or die;

$app = JFactory::getApplication();

// JLayout for standard handling of the details sidebar in administrator edit screens.
$published = $displayData->getForm()->getField('published');
$saveHistory = $displayData->get('state')->get('params')->get('save_history', 0);
?>
	<fieldset class="form-vertical form-no-margin">
		<?php if ($published) : ?>
			<?php echo $displayData->getForm()->renderField('published'); ?>
		<?php else : ?>
			<?php echo $displayData->getForm()->renderField('state'); ?>
		<?php endif; ?>

		<?php echo $displayData->getForm()->renderField('access'); ?>
		<?php echo $displayData->getForm()->renderField('catid'); ?>
		<?php echo $displayData->getForm()->renderField('featured'); ?>
		<?php if (JLanguageMultilang::isEnabled()) : ?>
			<?php echo $displayData->getForm()->renderField('language'); ?>
		<?php else : ?>
			<input type="hidden" id="jform_language" name="jform[language]" value="<?php echo $displayData->getForm()->getValue('language'); ?>">
		<?php endif; ?>
		
		<?php echo $displayData->getForm()->renderField('tags'); ?>
		<?php if ($saveHistory) : ?>
			<?php echo $displayData->getForm()->renderField('version_note'); ?>
		<?php endif; ?>
	</fieldset>
