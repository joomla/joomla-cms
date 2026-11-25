<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_newsfeeds
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

// Add strings for translations in Javascript.
Text::script('JGLOBAL_EXPAND_CATEGORIES');
Text::script('JGLOBAL_COLLAPSE_CATEGORIES');

/** @var \Joomla\Component\Newsfeeds\Site\View\Categories\HtmlView $this */
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('com_categories');
$wa->useScript('com_categories.shared-categories-accordion');

?>
<div class="com-newsfeeds-categories categories-list">
    <?php echo LayoutHelper::render('joomla.content.categories_default', $this); ?>
    <?php echo $this->loadTemplate('items'); ?>
</div>
