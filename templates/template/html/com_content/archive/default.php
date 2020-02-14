<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

JHtml::_('behavior.caption');
?>
<div class="archive<?php echo $this->pageclass_sfx; ?>">
<?php if ($this->params->get('show_page_heading')) : ?>
<div class="page-header">
<h1>
	<?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>
</div>
<?php endif; ?>
<form id="adminForm" action="<?php echo JRoute::_('index.php'); ?>" method="post" class="form-inline">
	<fieldset class="filters">
	<div class="filter-search">
		<?php if ($this->params->get('filter_field') !== 'hide') : ?>
		<label class="filter-search-lbl sr-only" for="filter-search"><?php echo JText::_('COM_CONTENT_TITLE_FILTER_LABEL') . '&#160;'; ?></label>
		<input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->filter); ?>" class="inputbox col-md-2" onchange="document.getElementById('adminForm').submit();" placeholder="<?php echo JText::_('COM_CONTENT_TITLE_FILTER_LABEL'); ?>">
		<?php endif; ?>

		<?php echo $this->form->monthField; ?>
		<?php echo $this->form->yearField; ?>
		<?php echo $this->form->limitField; ?>

		<button type="submit" class="btn btn-primary" style="vertical-align: top;"><?php echo JText::_('JGLOBAL_FILTER_BUTTON'); ?></button>
		<input type="hidden" name="view" value="archive">
		<input type="hidden" name="option" value="com_content">
		<input type="hidden" name="limitstart" value="0">
	</div>
	<br>
	</fieldset>

	<?php echo $this->loadTemplate('items'); ?>
</form>
</div>
