<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_categories
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;

?>

<?php foreach ($categories as $category) : ?>
    <li <?php if ($id == $category->id && in_array($view, ['category', 'categories']) && $option == 'com_content') {
        echo ' class="active"';
        } ?>> <?php $levelup = $category->level - $startLevel - 1; ?>
        <a href="<?php echo Route::_(RouteHelper::getCategoryRoute($category->id, $category->language)); ?>">
            <?php echo $category->title; ?>
            <?php if ($params->get('numitems')) : ?>
                (<?php echo $category->numitems; ?>)
            <?php endif; ?>
        </a>

        <?php if ($params->get('show_description', 0)) : ?>
            <?php echo HTMLHelper::_(
                'content.prepare',
                $category->description,
                $category->getParams(),
                'mod_articles_categories.content'
            ); ?>
        <?php endif; ?>
        <?php
        if (
            $params->get('show_children', 0) && (($params->get('maxlevel', 0) == 0)
                || ($params->get('maxlevel') >= ($category->level - $startLevel)))
            && count($category->getChildren())
        ) : ?>
            <?php echo '<ul>'; ?>
            <?php $temp = $categories; ?>
            <?php $categories = $category->getChildren(); ?>
            <?php require ModuleHelper::getLayoutPath(
                'mod_articles_categories',
                $params->get('layout', 'default') .
                '_items'
            ); ?>
            <?php $categories = $temp; ?>
            <?php echo '</ul>'; ?>
        <?php endif; ?>
    </li>
<?php endforeach; ?>
