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

// TODO: Make this js friendly
//JHtml::_('behavior.caption');
JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers');

// If the page class is defined, wrap the whole output in a div.
$pageClass = $this->params->get('pageclass_sfx');
?>
<?php if ($pageClass) : ?>
<div class="<?php echo $pageClass;?>">
<?php endif;?>

<?php if ($this->params->get('show_page_title', 1)) : ?>
<h1>
	<?php echo $this->escape($this->params->get('page_title')); ?>
</h1>
<?php endif; ?>

<?php if (!empty($this->lead_items)) : ?>
<ol class="jarticles-lead">
	<?php foreach ($this->lead_items as &$item) : ?>
	<li<?php echo $item->state == 0 ? 'system-unpublished' : null; ?>>
		<?php
			$this->item = &$item;
			echo $this->loadTemplate('item');
		?>
	</li>
	<?php endforeach; ?>
</ol>
<?php endif; ?>

<?php
// @TODO: Account for column separator. May need different arrangement for the down-then-across case.
?>
<?php if (!empty($this->lead_items)) : ?>
<ol class="jarticles columns-<?php echo (int) $this->columns;?>">
	<?php foreach ($this->intro_items as &$item) : ?>
	<li<?php echo $item->state == 0 ? 'system-unpublished' : null; ?>>
		<?php
			$this->item = &$item;
			echo $this->loadTemplate('item');
		?>
	</li>
	<?php endforeach; ?>
</ol>
<?php endif; ?>

<?php if (!empty($this->link_items)) : ?>
	<?php $this->loadTemplate('links'); ?>
<?php endif; ?>


<?php if ($this->params->def('show_pagination', 2) == 1  || ($this->params->get('show_pagination') == 2 && $this->pagination->get('pages.total') > 1)) : ?>
	<div>
		<?php echo $this->pagination->getPagesLinks(); ?>
	</div>
	<?php if ($this->params->def('show_pagination_results', 1)) : ?>
	<div>
		<?php echo $this->pagination->getPagesCounter(); ?>
	</div>
	<?php endif; ?>
<?php endif; ?>

<?php if ($pageClass) : ?>
</div>
<?php endif;?>
