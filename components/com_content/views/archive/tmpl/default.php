<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers');
$pageClass = $this->params->get('pageclass_sfx');
?>

<div class="archive<?php echo $pageClass;?>">
	<?php if ($this->params->get('show_page_title', 1)) : ?>
	<h1>
		<?php if ($this->escape($this->params->get('page_heading'))) :?>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		<?php else : ?>
			<?php echo $this->escape($this->params->get('page_title')); ?>
		<?php endif; ?>
	</h1>
	<?php endif; ?>
	
<form id="jForm" action="<?php JRoute::_('index.php')?>" method="post">	
	<fieldset class="filters">
	<legend class="element-invisible"><?php echo JText::_('JContent_Filter_Label'); ?></legend>
	<div class="filter-search">
		<?php if ($this->params->get('filter')) : ?>
		<label class="filter-search-lbl" for="filter-search"><?php echo JText::_('Content_'.$this->params->get('filter_field').'_Filter_Label').'&nbsp;'; ?></label>
		<input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->filter); ?>" class="inputbox" onchange="document.jForm.submit();" />
		<?php endif; ?>

		<?php echo $this->form->monthField; ?>
		<?php echo $this->form->yearField; ?>
		<?php echo $this->form->limitField; ?>
		<button type="submit" class="button"><?php echo JText::_('Filter'); ?></button>
	</div>
	</fieldset>

	<?php echo $this->loadTemplate('items'); ?>

	<input type="hidden" name="view" value="archive" />
	<input type="hidden" name="option" value="com_content" />
</form>
</div>
