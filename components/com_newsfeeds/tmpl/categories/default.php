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
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers');
HTMLHelper::_('behavior.core');

// Add strings for translations in Javascript.
Text::script('JGLOBAL_EXPAND_CATEGORIES');
Text::script('JGLOBAL_COLLAPSE_CATEGORIES');

HTMLHelper::_('script', 'com_newsfeeds/categories-default.js', ['relative' => true, 'version' => 'auto']);

?>
<div class="com-newsfeeds-categories categories-list">
	<?php echo LayoutHelper::render('joomla.content.categories_default', $this); ?>
	<?php echo $this->loadTemplate('items'); ?>
</div>
