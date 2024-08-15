<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_latest
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$moduleId = str_replace(' ', '', $module->title) . $module->id;

?>
<table class="table" id="<?php echo $moduleId; ?>">
    <caption class="visually-hidden"><?php echo $module->title; ?></caption>
    <thead>
        <tr>
            <th scope="col"><?php echo Text::_('JGLOBAL_TITLE'); ?></th>
            <?php if ($workflow_enabled) : ?>
                <th scope="col" class="w-20"><?php echo Text::_('JSTAGE'); ?></th>
            <?php endif; ?>
            <th scope="col" class="w-20"><?php echo Text::_('JAUTHOR'); ?></th>
            <th scope="col" class="w-20">
                <?php echo ($params->get('ordering', 'c_dsc') == 'm_dsc') ? Text::_('MOD_LATEST_HEADING_DATE_MODIFIED') : Text::_('MOD_LATEST_HEADING_DATE_CREATED'); ?>
            </th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($list)) : ?>
            <?php foreach ($list as $i => $item) : ?>
        <tr>
            <th scope="row">
                <?php if ($item->checked_out) : ?>
                    <?php echo HTMLHelper::_('jgrid.checkedout', $moduleId . $i, $item->editor, $item->checked_out_time, $module->id); ?>
                <?php endif; ?>
                <?php if ($item->link) : ?>
                    <a href="<?php echo $item->link; ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                <?php else : ?>
                    <?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
                <?php endif; ?>
            </th>
                <?php if ($workflow_enabled) : ?>
                <td>
                    <?php echo Text::_($item->stage_title); ?>
                </td>
                <?php endif; ?>
            <td>
                <?php echo $item->author_name; ?>
            </td>
            <td>
                <?php echo ($params->get('ordering', 'c_dsc') == 'm_dsc') ? HTMLHelper::_('date', $item->modified, Text::_('DATE_FORMAT_LC4')) : HTMLHelper::_('date', $item->created, Text::_('DATE_FORMAT_LC4')); ?>
            </td>
        </tr>
            <?php endforeach; ?>
        <?php else : ?>
        <tr>
            <td colspan="3">
                <?php echo Text::_('MOD_LATEST_NO_MATCHING_RESULTS'); ?>
            </td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>
