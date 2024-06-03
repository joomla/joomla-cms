<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_content
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;

if (!$list) {
    return;
}

?>
<div class="mod-content category-module mod-list">
    <?php if ($grouped) : ?>
        <?php foreach ($list as $groupName => $items) : ?>
            <div class="mod-content-group"><?php echo Text::_($groupName); ?></div>
                <?php require ModuleHelper::getLayoutPath('mod_content', $params->get('layout', 'default') . '_items'); ?>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <?php $items = $list; ?>
        <?php require ModuleHelper::getLayoutPath('mod_content', $params->get('layout', 'default') . '_items'); ?>
    <?php endif; ?>
</div>
