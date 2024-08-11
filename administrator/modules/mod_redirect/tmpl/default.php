<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_redirect
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

?>
<table class="table" id="<?php echo str_replace(' ', '', $module->title) . $module->id; ?>">
    <caption class="visually-hidden"><?php echo $module->title; ?></caption>
    <thead>
    <tr>
        <th scope="col" class="w-70"><?php echo Text::_('MOD_REDIRECT_HEADING_EXPIRED_URL'); ?></th>
        <th scope="col" class="w-10"><?php echo Text::_('JGLOBAL_HITS'); ?></th>
        <th scope="col" class="w-20"><?php echo Text::_('JDATE'); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php if (count($list)) : ?>
        <?php foreach ($list as $i => $item) : ?>
            <?php // Calculate popular items ?>
            <?php $hits = (int) $item->hits; ?>
            <?php $hits_class = ($hits >= 1000 ? 'danger' : ($hits >= 100 ? 'warning' : ($hits >= 10 ? 'info' : 'secondary'))); ?>
            <tr>
                <td>
                    <a href="<?php echo Route::_('index.php?option=com_redirect&task=link.edit&id=' . $item->id); ?>"
                       title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo htmlspecialchars($item->old_url, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($item->old_url, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                </td>
                <td>
                    <span class="badge bg-<?php echo $hits_class; ?>"><?php echo $item->hits; ?></span>
                </td>
                <td>
                    <?php echo HTMLHelper::_('date', $item->created_date, Text::_('DATE_FORMAT_LC4')); ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else : ?>
        <tr>
            <td colspan="2">
                <?php echo Text::_('MOD_REDIRECT_NO_MATCHING_RESULTS'); ?>
            </td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>
