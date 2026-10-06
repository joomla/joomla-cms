<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_category_tags
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

if (empty($item->items)) {
    return;
}

$items = $item->items;
?>
<ul>
    <?php foreach ($items as $item) :
        $title = htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8');
        $cat_id = $item->cat_id ? "&id=$item->cat_id" : '';
        $menuid = $Itemid ? '&Itemid=' . $Itemid : '' ;
        $link = "index.php?option=com_content&view=category$cat_id$menuid&filter_tag=$item->tag_id";
        ?>
    <li>
        <?php if ($categories_titles && $tree_display && empty($item->parent)) : ?>
            <span class="tag-category badge bg-info"><?= htmlspecialchars($item->cat_title, ENT_COMPAT, 'UTF-8') ?></span>
        <?php endif; ?>
    
        <a title="<?= $title?>" href="<?= $Itemid ? Route::_($link) : Uri::base() . $link ?>">

        <?php if ($image_display && isset($item->images)) :
            $item->params = new Registry($item->params);
            $item->images = json_decode($item->images, false);

            if ($item->images->image_intro ?? false) {
                $layoutAttr = [
                    'src'   => $item->images->image_intro,
                    'class' => 'tag-image ' . (empty($item->images->float_intro) ? $item->params->get('float_intro', false) : $item->images->float_intro),
                    'alt'   => empty($item->images->image_intro_alt) && empty($item->images->image_intro_alt_empty) ? false : $item->images->image_intro_alt
                ];

                echo LayoutHelper::render('joomla.html.image', array_merge($layoutAttr, ['itemprop' => 'thumbnail',]));
            }
        endif; ?>

        <?php if ($title_display) : ?>
            <span class="tag-title"><?= $title; ?></span>
        <?php endif; ?>

            <?php if ($count_display) : ?>
                <span class="tag-count badge bg-info"><?php echo $item->count; ?></span>
            <?php endif; ?>

            <?php if ($categories_titles && empty($tree_display)) : ?>
                <span class="tag-category badge bg-info"><?= htmlspecialchars($item->cat_title, ENT_COMPAT, 'UTF-8') ?></span>
            <?php endif; ?>
        </a>

        <?php require ModuleHelper::getLayoutPath('mod_category_tags', '_items'); ?>
    </li>
    <?php endforeach; ?>
</ul>
