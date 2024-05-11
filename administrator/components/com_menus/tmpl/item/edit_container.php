<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\Component\Menus\Administrator\Helper\MenusHelper;
use Joomla\Registry\Registry;

/** @var \Joomla\Component\Menus\Administrator\View\Item\HtmlView $this */

// Initialise related data.
$menuLinks = MenusHelper::getMenuLinks('main');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('joomla.treeselectmenu')
    ->useStyle('com_menus.admin-item-edit-container')
    ->useScript('com_menus.admin-item-edit-container');

?>
<div id="menuselect-group" class="control-group">
    <div class="control-label"><?php echo $this->form->getLabel('hideitems', 'params'); ?></div>

    <div id="jform_params_hideitems" class="controls">
        <?php if (!empty($menuLinks)) : ?>
            <?php $id = 'jform_params_hideitems'; ?>

        <div class="form-inline">
            <span class="small me-2"><?php echo Text::_('COM_MENUS_ACTION_EXPAND'); ?>:
                <a id="treeExpandAll" href="javascript://"><?php echo Text::_('JALL'); ?></a>,
                <a id="treeCollapseAll" href="javascript://"><?php echo Text::_('JNONE'); ?></a> |
                <?php echo Text::_('JSHOW'); ?>:
                <a id="treeUncheckAll" href="javascript://"><?php echo Text::_('JALL'); ?></a>,
                <a id="treeCheckAll" href="javascript://"><?php echo Text::_('JNONE'); ?></a>
            </span>
            <input type="text" id="treeselectfilter" name="treeselectfilter" class="form-control search-query"
                autocomplete="off" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>"
                aria-invalid="false" aria-label="<?php echo Text::_('JSEARCH_FILTER'); ?>">
        </div>

        <hr>
        <div class="alert alert-info">
            <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
            <?php echo Text::_('COM_MENUS_ITEM_FIELD_COMPONENTS_CONTAINER_HIDE_ITEMS_DESC'); ?>
        </div>
            <?php if (count($menuLinks)) : ?>
                <ul class="treeselect">
                    <?php $prevlevel = 0; ?>
                    <li>
                    <?php
                    $params      = new Registry($this->item->params);
                    $hiddenLinks = (array) $params->get('hideitems');

                    foreach ($menuLinks as $i => $link) : ?>
                        <?php
                        if ($extension = $link->element) :
                            $lang->load("$extension.sys", JPATH_ADMINISTRATOR)
                            || $lang->load("$extension.sys", JPATH_ADMINISTRATOR . '/components/' . $extension);
                        endif;

                        if ($prevlevel < $link->level) {
                            echo '<ul class="treeselect-sub">';
                        } elseif ($prevlevel > $link->level) {
                            echo str_repeat('</li></ul>', $prevlevel - $link->level);
                        } else {
                            echo '</li>';
                        }

                        $selected = in_array($link->value, $hiddenLinks) ? 1 : 0;
                        ?>
                            <li>
                                <div class="treeselect-item">
                                    <input type="checkbox" <?php echo $link->value > 1 ? ' name="jform[params][hideitems][]" ' : ''; ?>
                                        id="<?php echo $id . $link->value; ?>" value="<?php echo (int) $link->value; ?>" class="novalidate checkbox-toggle"
                                        <?php echo $selected ? ' checked="checked"' : ''; ?>>

                                    <?php if ($link->value == 1) : ?>
                                        <label for="<?php echo $id . $link->value; ?>" class="btn btn-sm btn-info"><?php echo Text::_('JALL') ?></label>
                                    <?php else : ?>
                                        <label for="<?php echo $id . $link->value; ?>" class="btn btn-sm btn-danger btn-hide"><?php echo Text::_('JHIDE') ?></label>
                                        <label for="<?php echo $id . $link->value; ?>" class="btn btn-sm btn-success btn-show"><?php echo Text::_('JSHOW') ?></label>
                                        <label for="<?php echo $id . $link->value; ?>"><?php echo Text::_($link->text); ?></label>
                                    <?php endif; ?>
                                </div>
                        <?php

                        if (!isset($menuLinks[$i + 1])) {
                            echo str_repeat('</li></ul>', $link->level);
                        }
                        $prevlevel = $link->level;
                        ?>
                    <?php endforeach; ?>
                    </li>
                </ul>
            <?php endif; ?>
        <joomla-alert id="noresultsfound" type="warning" style="display:none"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></joomla-alert>
        <?php endif; ?>
    </div>
</div>
