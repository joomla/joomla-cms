<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_tags_similar
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

if (!$list) {
    return;
}

?>
<ul class="mod-tagssimilar tagssimilar mod-list">
    <?php foreach ($list as $i => $item) : ?>
    <li>
        <?php if (($item->type_alias === 'com_users.category') || ($item->type_alias === 'com_banners.category')) : ?>
            <?php if (!empty($item->core_title)) : ?>
                <?php echo htmlspecialchars((string) $item->core_title, ENT_COMPAT, 'UTF-8'); ?>
            <?php endif; ?>
        <?php else : ?>
            <a href="<?php echo Route::_($item->link); ?>">
                <?php if (!empty($item->core_title)) : ?>
                    <?php echo htmlspecialchars((string) $item->core_title, ENT_COMPAT, 'UTF-8'); ?>
                <?php endif; ?>
            </a>
        <?php endif; ?>
    </li>
    <?php endforeach; ?>
</ul>
