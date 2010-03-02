<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_newsfeeds
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

$pageClass = $this->params->get('pageclass_sfx');
?>

<div class="newsfeed-category<?php echo $pageClass;?>">

<?php if ($this->params->def('show_page_title', 1)) : ?>
	<h1>
		<?php if ($this->escape($this->params->get('page_heading'))) :?>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		<?php else : ?>
			<?php echo $this->escape($this->params->get('page_title')); ?>
		<?php endif; ?>
	</h1>
<?php endif; ?>
	<h2>
		<?php echo $this->escape($this->category->title); ?>
	</h2>
	<?php if ($this->params->def('show_description', 1) || $this->params->def('show_description_image', 1)) : ?>
		<div class="category_desc">
			<!-- @TODO Verify image path defaults/overrides/positions + category_params breaks display-->
			<?php if ($this->params->get('show_description_image') && $this->category->category_params->get('image')) : ?>
				<img src="<?php echo $this->category->category_params->get('image'); ?>"/>
			<?php endif; ?>
			<?php if ($this->params->get('show_description') && $this->category->description) : ?>
				<?php echo $this->category->description; ?>
			<?php endif; ?>
			<div class="clr"></div>
		</div>
	<?php endif; ?>

<?php echo $this->loadTemplate('items'); ?>

<!-- <div class="cat-siblings">  -->
<?php  // echo $this->loadTemplate('siblings');  ?>
<!-- </div>  -->

<!--  <div class="cat-children"> -->
<?php // echo $this->loadTemplate('children'); ?>
<!--  </div> -->

<!--  <div class="cat-parents"> -->
<?php  // echo $this->loadTemplate('parents');  ?>
<!--  </div> -->

</div>

