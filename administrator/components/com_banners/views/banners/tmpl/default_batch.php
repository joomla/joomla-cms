<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

$published = $this->state->get('filter.published');
?>
<fieldset class="batch">
	<legend><?php echo JText::_('COM_BANNERS_BATCH_OPTIONS');?></legend>
	<p><?php echo JText::_('COM_BANNERS_BATCH_TIP'); ?></p>
	<?php echo JHtml::_('banner.clients');?>
	<?php echo JHtml::_('batch.language');?>

	<?php if ($published >= 0) : ?>
		<?php echo JHtml::_('batch.item', 'com_banners');?>
	<?php endif; ?>

	<button type="submit" onclick="Joomla.submitbutton('banner.batch');">
		<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
	</button>
	<button type="button" onclick="document.id('batch-category-id').value='';document.id('batch-client-id').value='';document.id('batch-language-id').value=''">
		<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
	</button>
</fieldset>
