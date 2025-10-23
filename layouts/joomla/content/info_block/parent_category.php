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

?>
<dd class="parent-category-name">
    <?php echo LayoutHelper::render('joomla.icon.iconclass', ['icon' => 'icon-folder icon-fw']); ?>
    <?php $title = $this->escape($displayData['item']->parent_title); ?>
    <?php if ($displayData['params']->get('link_parent_category') && !empty($displayData['item']->parent_id)) : ?>
        <?php $url = '<a href="' . Route::_(
            RouteHelper::getCategoryRoute($displayData['item']->parent_id, $displayData['item']->parent_language)
        )
            . '">' . $title . '</a>'; ?>
        <?php echo Text::sprintf('COM_CONTENT_PARENT', $url); ?>
    <?php else : ?>
        <?php echo Text::sprintf('COM_CONTENT_PARENT', '<span>' . $title . '</span>'); ?>
    <?php endif; ?>
</dd>
