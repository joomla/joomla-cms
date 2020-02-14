<?php

/**
 * @copyright    Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license    GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('JPATH_PLATFORM') or die;
?>
    <div class="uk-grid">
        <label class="uk-form-label uk-width-2-10" for="cols">
            <?php echo JText::_('WF_TABLE_COLS'); ?></label>
        <div class="uk-form-controls uk-width-3-10">
            <input id="cols" type="number" min="1" value="" required />
        </div>

        <label class="uk-form-label uk-width-2-10" for="rows">
            <?php echo JText::_('WF_TABLE_ROWS'); ?></label>
        <div class="uk-form-controls uk-width-3-10">
            <input id="rows" type="number" value="" required />
        </div>
    </div>

<div class="uk-grid">
    <label class="uk-form-label uk-width-2-10" for="cellpadding">
        <?php echo JText::_('WF_TABLE_CELLPADDING'); ?></label>
    <div class="uk-form-controls uk-width-3-10">
        <input id="cellpadding" type="number" value="" />
    </div>

    <label class="uk-form-label uk-width-2-10" for="cellspacing">
        <?php echo JText::_('WF_TABLE_CELLSPACING'); ?></label>
    <div class="uk-form-controls uk-width-3-10">
        <input id="cellspacing" type="number" value="" />
    </div>
</div>
<div class="uk-grid">
    <label class="uk-form-label uk-width-2-10" for="align">
        <?php echo JText::_('WF_TABLE_ALIGN'); ?></label>
    <div class="uk-form-controls uk-width-3-10">
        <select id="align">
            <option value=""><?php echo JText::_('WF_OPTION_NOT_SET'); ?></option>
            <option value="center"><?php echo JText::_('WF_TABLE_ALIGN_MIDDLE'); ?></option>
            <option value="left"><?php echo JText::_('WF_TABLE_ALIGN_LEFT'); ?></option>
            <option value="right"><?php echo JText::_('WF_TABLE_ALIGN_RIGHT'); ?></option>
        </select>
    </div>

    <label class="uk-form-label uk-width-2-10" for="table_border">
        <?php echo JText::_('WF_TABLE_BORDER'); ?></label>
    <div class="uk-form-controls uk-width-3-10">
        <input id="table_border" type="number" value="" />
    </div>
</div>
<div class="uk-grid">
    <label class="uk-form-label uk-width-2-10" for="width">
        <?php echo JText::_('WF_TABLE_WIDTH'); ?></label>
    <div class="uk-form-controls uk-width-3-10">
        <input type="text" id="width" value="" />
    </div>

    <label class="uk-form-label uk-width-2-10" for="height">
        <?php echo JText::_('WF_TABLE_HEIGHT'); ?></label>
    <div class="uk-form-controls uk-width-3-10">
        <input type="text" id="height" value="" />
    </div>
</div>
<div class="uk-form-row">
    <input id="caption" type="checkbox" />
    <label for="caption">
        <?php echo JText::_('WF_TABLE_CAPTION'); ?></label>
</div>
