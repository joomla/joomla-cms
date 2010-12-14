<?php
/**
 * @version		
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
$cparams = JComponentHelper::getParams('com_media');
?>
<?php if ($this->params->get('show_page_heading', 1)) : ?>

<div class="componentheading<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>"> <?php echo $this->escape($this->params->get('page_title')); ?> </div>
<?php endif; ?>
<div class="blog<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>" cellpadding="0" cellspacing="0">
  <?php if ($this->params->def('show_description', 1) || $this->params->def('show_description_image', 1)) :?>
  <?php if ($this->params->get('show_description_image') && $this->category->getParams()->get('image')) : ?>
  <img src="<?php echo $this->category->getParams()->get('image'); ?>"/>
  <?php endif; ?>
  <?php if ($this->params->get('show_description') && $this->category->description) : ?>
  <?php echo JHtml::_('content.prepare', $this->category->description); ?>
  <?php endif; ?>
  <br />
  <br />
  <?php endif; ?>
  <?php $leadingcount=0 ; ?>
  <?php if (!empty($this->lead_items)) : ?>
  <div class="items-leading">
    <?php foreach ($this->lead_items as &$item) : ?>
    <div class="leading-<?php echo $leadingcount; ?><?php echo $item->state == 0 ? 'class="system-unpublished"' : null; ?>">
      <?php
				$this->item = &$item;
				echo $this->loadTemplate('item');
			?>
    </div>
    <?php
			$leadingcount++;
		?>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <?php
	$introcount=(count($this->intro_items)); 
	$counter=0;
?>
  <?php if (!empty($this->intro_items)) : ?>
  <?php foreach ($this->intro_items as $key => &$item) : ?>
  <?php
		$key= ($key-$leadingcount)+1; 
		$rowcount=( ((int)$key-1) %	(int) $this->columns) +1;
		$row = $counter / $this->columns ; ?>
  <div class="items-row cols-<?php echo (int) $this->columns;?> <?php // echo 'column-'.$rowcount ; ?>">
    <div class="item column-<?php echo $rowcount;?><?php echo $item->state == 0 ? ' system-unpublished' : null; ?>">
      <?php
			$this->item = &$item;
			echo $this->loadTemplate('item');
		?>
    </div>
    <?php $counter++; ?>
    <?php if (($rowcount == $this->columns) or ($counter ==$introcount)): ?>
  </div>
  <span class="row-separator"></span> </div>
<div class="clr"></div>
<?php endif; ?>
<?php endforeach; ?>
<?php  endif; ?>
<div class="blog_more<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
  <?php if (!empty($this->link_items)) : ?>
  <?php echo $this->loadTemplate('links'); ?>
  <?php endif; ?>

<?php if ($this->params->get('show_pagination')) : ?>
<?php echo $this->pagination->getPagesLinks(); ?> <br />
<br />
<?php endif; ?>
<?php if ($this->params->get('show_pagination_results')) : ?>
<?php echo $this->pagination->getPagesCounter(); ?>
<?php endif; ?>
</div>


