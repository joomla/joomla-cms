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

<?php if (($this->params->def('image', -1) != -1)) : ?>
	<?php
		// Define image tag attributes
		$attribs['align']	= $this->params->get('image_align');
		$attribs['hspace']	= 6;

		// Use the static HTML library to build the image tag
		echo JHtml::_('image', 'images/stories/'.$this->params->get('image'), JText::_('Web Links'), $attribs);
	?>
<?php endif; ?>

<?php if ($this->params->def('show_comp_description', 1)) : ?>
	<p>
		<?php echo $this->params->def('comp_description', JText::_('WEBLINKS_DESC')); ?>
	</p>
<?php endif; ?>

<ul>
<?php foreach ($this->items as $item) : ?>
	<li>
		<a href="<?php echo JRoute::_('index.php?option=com_weblinks&view=category&id='.$item->slug); ?>" class="category<?php echo $this->params->get('pageclass_sfx'); ?>">
			<?php echo $this->escape($item->title);?></a>
		&nbsp;
		<span class="small">(<?php echo $item->numlinks;?>)</span>
	</li>
<?php endforeach; ?>
</ul>

<?php if ($pageClass) : ?>
</div>
<?php endif;?>
