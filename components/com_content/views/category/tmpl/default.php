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

<h2>
	<?php echo $this->escape($this->item->title); ?>
</h2>

<?php echo $this->item->description; ?>

<?php echo $this->loadTemplate('articles'); ?>

<?php echo $this->loadTemplate('siblings'); ?>

<?php echo $this->loadTemplate('children'); ?>

<?php echo $this->loadTemplate('parents'); ?>

<?php if ($pageClass) : ?>
</div>
<?php endif;?>