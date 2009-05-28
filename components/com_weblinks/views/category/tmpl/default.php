<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_weblinks
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

// If the page class is defined, wrap the whole output in a div.
$pageClass = $this->params->get('pageclass_sfx');
?>
<?php if ($pageClass) : ?>
<div class="<?php echo $pageClass;?>">
<?php endif;?>

<?php if ($this->params->def('show_page_title', 1)) : ?>
	<h1>
		<?php echo $this->escape($this->params->get('page_title')); ?>
	</h1>
<?php endif; ?>

<?php if ($this->category->image) : ?>
	<?php
		// Define image tag attributes
		$attribs['align']	= $this->category->image_position;
		$attribs['hspace']	= 6;

		// Use the static HTML library to build the image tag
		echo JHtml::_('image', 'images/stories/'.$this->category->image, JText::_('Web Links'), $attribs);
	?>
<?php endif; ?>

<?php if ($this->category->description) : ?>
	<p>
		<?php echo $this->category->description; ?>
	</p>
<?php endif; ?>

<?php echo $this->loadTemplate('items'); ?>

<?php if ($this->params->get('show_other_cats', 1)): ?>
	<ul>
	<?php foreach ($this->categories as $category) : ?>
		<li>
			<a href="<?php echo JRoute::_('index.php?view=category&id='.$category->slug); ?>">
				<?php echo $this->escape($category->title);?></a>
			<small>(<?php echo $category->numlinks;?>)</small>
		</li>
	<?php endforeach; ?>
	</ul>
<?php endif; ?>

<?php if ($pageClass) : ?>
</div>
<?php endif;?>
