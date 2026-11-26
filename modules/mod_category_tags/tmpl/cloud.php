<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_category_tags
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$minsize = $params->get('minsize', 1);
$maxsize = $params->get('maxsize', 2);

?>
<div class="mod-tagspopular-cloud tagspopular tagscloud">
<?php
if (!count($list)) : ?>
    <div class="alert alert-info">
        <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
        <?php echo Text::_('MOD_CATEGORY_TAGS_NO_ITEMS_FOUND'); ?>
    </div>
<?php else :
    // Find maximum and minimum count
    $mincount = null;
    $maxcount = null;

    foreach ($list as $item) {
        if ($mincount === null || $mincount > $item->count) {
            $mincount = $item->count;
        }

        if ($maxcount === null || $maxcount < $item->count) {
            $maxcount = $item->count;
        }
    }

    $countdiff = $maxcount - $mincount;

    foreach ($list as $item) :
        if ($countdiff === 0) :
            $fontsize = $minsize;
        else :
            $fontsize = $minsize + (($maxsize - $minsize) / $countdiff) * ($item->count - $mincount);
        endif;
        $cat_id = $item->cat_id ? "&id=$item->cat_id" : '';
        ?>
        <span class="tag">
            <a class="tag-name" style="font-size: <?php echo $fontsize . 'em'; ?>" href="<?= Route::_("index.php?option=com_content&view=category&layout=blog$cat_id&filter_tag=$item->tag_id"); ?>">
                <?php echo htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8'); ?></a>
            <?php if ($count_display) : ?>
                <span class="tag-count badge bg-info"><?php echo $item->count; ?></span>
            <?php endif; ?>
        </span>
    <?php endforeach; ?>
<?php endif; ?>
</div>
