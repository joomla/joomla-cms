<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_tags_popular
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Tags\Site\Helper\RouteHelper;

?>
<div class="mod-tagspopular tagspopular">
<?php if (!count($list)) : ?>
    <div class="alert alert-info">
        <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
        <?php echo Text::_('MOD_TAGS_POPULAR_NO_ITEMS_FOUND'); ?>
    </div>
<?php else : ?>
    <ul>
    <?php foreach ($list as $item) : ?>
    <li>
        <a href="<?php echo Route::_(RouteHelper::getComponentTagRoute($item->tag_id . ':' . $item->alias, $item->language)); ?>">
            <?php echo htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8'); ?></a>
        <?php if ($display_count) : ?>
            <span class="tag-count badge bg-info"><?php echo $item->count; ?></span>
        <?php endif; ?>
    </li>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>
</div>
