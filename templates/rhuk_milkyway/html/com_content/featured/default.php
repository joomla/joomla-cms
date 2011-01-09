<?php
/**
 * @version		
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;?>
<?php 
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');
JHtml::_('behavior.tooltip');
JHtml::core();
?>
<?php if ($this->params->get('show_page_heading', 1)) : ?>
<div class="componentheading<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
	<?php echo $this->escape($this->params->get('page_heading')); ?>
</div>
<?php endif; ?>

<div class="blog<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>" >
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
  <div class="items-row cols-<?php echo (int) $this->columns;?>">
    <div class="item column-<?php echo $rowcount;?><?php echo $item->state == 0 ? ' system-unpublished' : null; ?>">
      <?php
			$this->item = &$item;
			echo $this->loadTemplate('item');
		?>
    </div>
    <?php $counter++; ?>
    <?php if (($rowcount == $this->columns) or ($counter ==$introcount)): ?>
  </div>
  <span class="row-separator"></span> 
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
