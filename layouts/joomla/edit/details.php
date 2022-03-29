<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @deprecated  3.2 removed without replacement in 4.0 see global.php for an alternative
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
			<?php echo $displayData->getForm()->renderField('published'); ?>
		<?php else : ?>
			<?php echo $displayData->getForm()->renderField('state'); ?>
		<?php endif; ?>

		<?php echo $displayData->getForm()->renderField('access'); ?>
		<?php echo $displayData->getForm()->renderField('featured'); ?>
		<?php if (JLanguageMultilang::isEnabled()) : ?>
			<?php echo $displayData->getForm()->renderField('language'); ?>
		<?php else : ?>
			<input type="hidden" id="jform_language" name="jform[language]" value="<?php echo $displayData->getForm()->getValue('language'); ?>" />
		<?php endif; ?>
		
		<?php echo $displayData->getForm()->renderField('tags'); ?>
		<?php if ($saveHistory) : ?>
			<?php echo $displayData->getForm()->renderField('version_note'); ?>
		<?php endif; ?>
	</fieldset>
</div>
