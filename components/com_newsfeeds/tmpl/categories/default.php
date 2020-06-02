<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

// Add strings for translations in Javascript.
Text::script('JGLOBAL_EXPAND_CATEGORIES');
Text::script('JGLOBAL_COLLAPSE_CATEGORIES');

$this->document->getWebAssetManager()->registerAndUseScript(
	'com_categories.shared-categories-accordion',
	'com_categories/shared-categories-accordion.js',
	[],
	['defer' => true],
	['core']
);

?>
<div class="com-newsfeeds-categories categories-list">
	<?php echo LayoutHelper::render('joomla.content.categories_default', $this); ?>
	<?php echo $this->loadTemplate('items'); ?>
</div>
