<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;

$categories = [];

foreach (($displayData['item']->secondary_categories ?? []) as $category) {
    $title = $this->escape($category->title);

    if ($displayData['params']->get('link_secondary_categories') && !empty($category->id)) {
        $categories[] = '<a href="' . $this->escape(Route::_(
            RouteHelper::getCategoryRoute($category->id, $category->language)
        )) . '">' . $title . '</a>';
    } else {
        $categories[] = '<span>' . $title . '</span>';
    }
}

if (empty($categories)) {
    return;
}
?>

<dd class="secondary-categories-name">
    <?php echo LayoutHelper::render('joomla.icon.iconclass', ['icon' => 'icon-folder-open icon-fw']); ?>
    <?php if (count($categories) > 1) : ?>
        <?php echo Text::sprintf('COM_CONTENT_SECONDARY_CATEGORIES', implode(', ', $categories)); ?>
    <?php else : ?>
        <?php echo Text::sprintf('COM_CONTENT_SECONDARY_CATEGORY', $categories[0]); ?>
    <?php endif; ?>
</dd>
