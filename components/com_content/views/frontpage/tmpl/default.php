<?php
/**
 * @version		$Id$
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
<h2>
	<?php echo $this->escape($this->params->get('page_title')); ?>
</h2>
<?php endif; ?>

<!-- Leading Articles -->
<?php if (!empty($this->lead_items)) : ?>
<ol class="jarticles-lead">
	<?php foreach ($this->lead_items as &$item) : ?>
	<li<?php echo $item->state == 0 ? ' class="system-unpublished"' : null; ?>>
		<?php
			$this->item = &$item;
			echo $this->loadTemplate('item');
		?>
	</li>
	<?php endforeach; ?>
</ol>
<?php endif; ?>

<!-- Intro'd Articles -->

<?php if (!empty($this->intro_items)) : ?>
<ol class="jarticles-intro jcols-<?php echo (int) $this->columns;?>">
	<?php foreach ($this->intro_items as $key => &$item) : ?>
	<li class="jcolumn-<?php echo (((int)$key - 1) % (int) $this->columns)+1;?><?php echo $item->state == 0 ? ' system-unpublished"' : null; ?>">
		<?php
			$this->item = &$item;
			echo $this->loadTemplate('item');
		?>
	</li>
	<?php endforeach; ?>
</ol>

<?php endif; ?>

<?php if (!empty($this->link_items)) : ?>
	<div class="jarticles-more">
	<?php echo $this->loadTemplate('links'); ?>
	</div>
<?php endif; ?>


<?php if ($this->params->def('show_pagination', 2) == 1  || ($this->params->get('show_pagination') == 2 && $this->pagination->get('pages.total') > 1)) : ?>
	<div class="jpagination">
		<?php echo $this->pagination->getPagesLinks(); ?>
		<?php if ($this->params->def('show_pagination_results', 1)) : ?>
			<div class="jpag-results">
				<?php echo $this->pagination->getPagesCounter(); ?>
			</div>
		<?php endif; ?>
	</div>
<?php endif; ?>

</div>

