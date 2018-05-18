<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
JHtml::_('behavior.caption');
JHtml::_('behavior.core');

// Add strings for translations in Javascript.
JText::script('JGLOBAL_EXPAND_CATEGORIES');
JText::script('JGLOBAL_COLLAPSE_CATEGORIES');

HTMLHelper::_('script', 'com_newsfeeds/categories-default.js', ['relative' => true, 'version' => 'auto']);

?>
<div class="categories-list">
	<?php echo JLayoutHelper::render('joomla.content.categories_default', $this); ?>
	<?php echo $this->loadTemplate('items'); ?>
</div>
