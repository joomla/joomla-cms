<?php
/**
 * @version		$Id: featured.php 12450 2009-07-05 03:45:24Z eddieajau $
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers');

// If the page class is defined, add to class as suffix.
// It will be a separate class if the user starts it with a space
$pageClass = $this->params->get('pageclass_sfx');
?>

<div class="jarticles-featured<?php echo $pageClass;?>">

<?php if ($this->params->get('show_page_title', 1)) : ?>
<h1>
	<?php if ($this->escape($this->params->get('page_heading'))) :?>
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	<?php else : ?>
		<?php echo $this->escape($this->params->get('page_title')); ?>
	<?php endif; ?>
</h1>
<?php endif; ?>

<?php if (!empty($this->lead_items)) : ?>
<div class="jarticles-lead">
        <?php foreach ($this->lead_items as &$item) : ?>
        <div <?php echo $item->state == 0 ? ' class="system-unpublished"' : null; ?>>
                <?php
                        $this->item = &$item;
                        echo $this->loadTemplate('item');
                ?>
        </div>
        <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (!empty($this->intro_items)) : ?>
<div class="jarticles-intro jcols-<?php echo (int) $this->columns;?>">
	<?php foreach ($this->intro_items as $key => &$item) : ?>
	<div class="jcolumn-<?php echo (((int)$key - 1) % (int) $this->columns)+1;?><?php echo $item->state == 0 ? ' system-unpublished"' : null; ?>">
		<?php
			$this->item = &$item;
			echo $this->loadTemplate('item');
		?>
	</div>
	<?php endforeach; ?>
</div>


<?php endif; ?>

<?php if (!empty($this->link_items)) : ?>
        <div class="jarticles_more">
        <?php echo $this->loadTemplate('links'); ?>
        </div>
<?php endif; ?>

<?php  // if ($this->params->def('show_pagination', 2) == 1  || ($this->params->get('show_pagination') == 2 && $this->pagination->get('pages.total') > 1)) : ?>
        <div class="jpagination">
                <?php // echo $this->pagination->getPagesLinks(); ?>
                <?php // if ($this->params->def('show_pagination_results', 1)) : ?>
                        <p class="counter">
                                <?php // echo $this->pagination->getPagesCounter(); ?>
                        </p>
                <?php // endif; ?>
                   <?php // echo $this->pagination->getPagesLinks(); ?>
        </div>
<?php //  endif;   ?>

</div>

