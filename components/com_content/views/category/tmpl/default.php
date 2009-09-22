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

$pageClass = $this->params->get('pageclass_sfx');
?>

<div class="jcategory<?php echo $pageClass;?>">

<?php if ($this->params->get('show_page_title', 1)) : ?>
<h2>
	<?php echo $this->escape($this->params->get('page_title')); ?>
</h2>
<?php endif; ?>

<h3>
	<?php echo $this->escape($this->item->title); ?>
</h3>

<?php if ($this->params->get('show_description') && $this->category->description) : ?>
	<?php echo $this->item->description; ?>
	
<?php endif; ?>

<div class="jcat-articles">
<?php echo $this->loadTemplate('articles'); ?>
</div>

<div class="jcat-siblings">
<?php /* echo $this->loadTemplate('siblings'); */?>
</div> 

<div class="jcat-children">
<?php echo $this->loadTemplate('children'); ?>
</div>

<div class="jcat-parents">
<?php /* echo $this->loadTemplate('parents'); */ ?>
</div> 

</div>
