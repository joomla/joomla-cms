<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_weblinks
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
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
	<h2>
		<?php echo $this->escape($this->params->get('page_title')); ?>
	</h2>
<?php endif; ?>
<?php  /**
TODO fix images in com_categories ?>
<?php if ($this->category->image) : ?>
	<?php
		// Define image tag attributes
		$attribs['align']	= $this->category->image_position;
		$attribs['hspace']	= 6;

		// Use the static HTML library to build the image tag
		echo JHtml::_('image', 'images/stories/'.$this->category->image, JText::_('Web Links'), $attribs);
	?>
<?php endif; ?>
<?php  **/ ?>
<?php if ($this->category->description) : ?>
	<p>
		<?php echo $this->category->description; ?>
	</p>
<?php endif; ?>

<?php echo $this->loadTemplate('items'); ?>

<div class="jcat-siblings">
<?php /* echo $this->loadTemplate('siblings'); */?>
</div> 

<div class="jcat-children">
<?php echo $this->loadTemplate('children'); ?>
</div>

<div class="jcat-parents">
<?php /* echo $this->loadTemplate('parents'); */ ?>
</div> 


<?php if ($pageClass) : ?>
</div>
<?php endif;?>
