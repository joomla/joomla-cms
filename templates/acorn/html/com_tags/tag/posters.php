<?php
/**
 * @subpackage  com_tags
 * @copyright   Copyright (C) 2013 Troy T. Hall All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
JHtml::_('behavior.core');
JHtml::_('formbehavior.chosen', 'select');

$doc             = JFactory::getDocument();
$app             = JFactory::getApplication( 'site' );
$template        = $app->getTemplate(true);
$item            = $this -> item;
$usetagTitle     = $template->params->get('usetagTitle');
$tagtitle        = $this->escape($item[0]->title);
$showpageHeading = $this->params->get('show_page_heading');
$templatePath    = JURI::root() . 'templates/' . JFactory::getApplication()->getTemplate();

$doc->addScript( $templatePath . '/js/isotope.pkgd.min.js' );
$doc->addScript( $templatePath . '/js/isotope-layout.js' );

?>

<div class="acorn-portfolio <?php echo $this->pageclass_sfx; ?>">
<?php if ($this->params->get('show_page_heading')) : ?>
<div class="headline">
	<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
</div>
<?php endif; ?>

<?php if ( $usetagTitle && !$this->params->get('show_page_heading')) : ?>
<div class="headline">
	<h2><?php echo $tagtitle; ?></h2>
</div>
<?php endif; ?>



<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>"

      method="post" name="adminForm" id="adminForm" class="form-inline">
<?php if ($this->params->get('show_headings') || $this->params->get('filter_field') || $this->params->get('show_pagination_limit')) : ?>
<fieldset class="filters btn-toolbar">
	<?php if ($this->params->get('filter_field')) :?>
		<div class="btn-group">
			<label class="filter-search-lbl element-invisible" for="filter-search">
				<?php echo JText::_('COM_TAGS_TITLE_FILTER_LABEL') . '&#160;'; ?>
			</label>
			<input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->state->get('list.filter')); ?>" class="inputbox" onchange="document.adminForm.submit();" title="<?php echo JText::_('COM_TAGS_FILTER_SEARCH_DESC'); ?>" placeholder="<?php echo JText::_('COM_TAGS_TITLE_FILTER_LABEL'); ?>" />
		</div>
	<?php endif; ?>
	<?php if ($this->params->get('show_pagination_limit')) : ?>
		<div class="btn-group pull-right">
			<label for="limit" class="element-invisible">
				<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>
			</label>
			<?php echo $this->pagination->getLimitBox(); ?>
		</div>
	<?php endif; ?>


	<input type="hidden" name="filter_order" value="" />
	<input type="hidden" name="filter_order_Dir" value="" />
	<input type="hidden" name="limitstart" value="" />
	<input type="hidden" name="task" value="" />
	<div class="clearfix"></div>
</fieldset>
<?php endif; ?>

    <div class="clearfix"></div>
<?php if (empty($this -> items)) : ?>
	<p> <?php echo JText::_('COM_TAGS_NO_ITEMS'); ?></p>
<?php else : ?>
<div class="isotope">
	<div id="isotope-container" class="clearfix">
	<!-- begin portfolio items -->
	<?php

        foreach ($this -> items as &$item) :

            $this -> item = $item;

            echo $this -> loadTemplate('item');
	endforeach;

?>
	<!-- end portfolio items -->
	</div>
</div>

    <?php endif; ?>

</form>
<div class="clearfix"></div>
<?php if (($this->params->def('show_pagination', 1) == 1 || ($this->params->get('show_pagination') == 2)) && ($this->pagination->get('pages.total') > 1)) : ?>
	<div class="pagination">
		<?php if ($this->params->def('show_pagination_results', 1)) : ?>
			<p class="counter pull-right"> <?php echo $this->pagination->getPagesCounter(); ?> </p>
		<?php endif; ?>
		<?php echo $this->pagination->getPagesLinks(); ?>
	</div>
<?php endif; ?>

</div>

