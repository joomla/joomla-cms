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

// If the page class is defined, add to class as suffix.
// It will be a separate class if the user starts it with a space
$pageClass = $this->params->get('pageclass_sfx');
?>
<div class="blog-featured<?php echo $pageClass;?>">
<?php if ($this->params->get('show_page_heading')!=0 or $this->params->get('show_category_title')): ?>
<h1>

<?php if ( $this->params->get('show_page_heading')!=0) : ?>
	<?php echo $this->escape($this->params->get('page_heading')); ?>
<?php endif; ?>
	<?php if ($this->params->get('show_category_title')) :?>


	<?php	echo '<span class="subheading-category">'.$this->category->title.'</span>'; ?>
	<?php endif; ?>

</h1>
<?php endif; ?>
<?php echo $this->loadTemplate('items'); ?>
<?php if ($this->params->def('show_pagination', 2) == 1  || ($this->params->get('show_pagination') == 2 && $this->pagination->get('pages.total') > 1)) : ?>
	<div class="pagination">

		<?php if ($this->params->def('show_pagination_results', 1)) : ?>
			<p class="counter">
				<?php echo $this->pagination->getPagesCounter(); ?>
			</p>
		<?php  endif; ?>
				<?php echo $this->pagination->getPagesLinks(); ?>
	</div>
<?php endif; ?>

</div>

