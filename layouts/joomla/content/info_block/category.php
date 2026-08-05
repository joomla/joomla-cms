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

$title = $this->escape($displayData['item']->category_title);

if ($displayData['params']->get('link_category') && !empty($displayData['item']->catid)) {
    $categories[] = '<a href="' . Route::_(
        RouteHelper::getCategoryRoute($displayData['item']->catid, $displayData['item']->category_language)
    )
        . '">' . $title . '</a>';
} else {
    $categories[] = '<span>' . $title . '</span>';
}

if ((int) $displayData['params']->get('include_secondary_categories', 1)) {
    foreach (($displayData['item']->secondary_categories ?? []) as $category) {
        $title = $this->escape($category->title);

        if ($displayData['params']->get('link_category') && !empty($category->id)) {
            $categories[] = '<a href="' . Route::_(
                RouteHelper::getCategoryRoute($category->id, $category->language)
            )
                . '">' . $title . '</a>';
        } else {
            $categories[] = '<span>' . $title . '</span>';
        }
    }
}

?>
<dd class="category-name">
    <?php echo LayoutHelper::render('joomla.icon.iconclass', ['icon' => 'icon-folder-open icon-fw']); ?>
    <?php if (count($categories) > 1) : ?>
        <?php echo Text::sprintf('COM_CONTENT_CATEGORIES', implode(', ', $categories)); ?>
    <?php else : ?>
        <?php echo Text::sprintf('COM_CONTENT_CATEGORY', implode(', ', $categories)); ?>
    <?php endif; ?>
</dd>
