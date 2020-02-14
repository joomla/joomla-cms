<?php

/**
 * @copyright 	Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('JPATH_PLATFORM') or die;
?>
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

        <label class="uk-form-label uk-width-2-10" for="celltype">
                <?php echo JText::_('WF_TABLE_CELL_TYPE'); ?></label>
        <div class="uk-form-controls uk-width-3-10">
            <select id="celltype" >
                <option value="td"><?php echo JText::_('WF_TABLE_TD'); ?></option>
                <option value="th"><?php echo JText::_('WF_TABLE_TH'); ?></option>
            </select>
        </div>
    </div>
    
    <div class="uk-grid">
        <label class="uk-form-label uk-width-2-10" for="valign">
                <?php echo JText::_('WF_TABLE_VALIGN'); ?></label>
        <div class="uk-form-controls uk-width-3-10">
            <select id="valign" >
                <option value=""><?php echo JText::_('WF_OPTION_NOT_SET'); ?></option>
                <option value="top"><?php echo JText::_('WF_TABLE_ALIGN_TOP'); ?></option>
                <option value="middle"><?php echo JText::_('WF_TABLE_ALIGN_MIDDLE'); ?></option>
                <option value="bottom"><?php echo JText::_('WF_TABLE_ALIGN_BOTTOM'); ?></option>
            </select>
        </div>

        <label class="uk-form-label uk-width-2-10" for="scope">
                <?php echo JText::_('WF_TABLE_SCOPE'); ?></label>
        <div class="uk-form-controls uk-width-3-10">
            <select id="scope" >
                <option value=""><?php echo JText::_('WF_OPTION_NOT_SET'); ?></option>
                <option value="col"><?php echo JText::_('WF_TABEL_COL'); ?></option>
                <option value="row"><?php echo JText::_('WF_TABEL_ROW'); ?></option>
                <option value="rowgroup"><?php echo JText::_('WF_TABLE_ROWGROUP'); ?></option>
                <option value="colgroup"><?php echo JText::_('WF_TABLE_COLGROUP'); ?></option>
            </select>
        </div>
    </div>

    <div class="uk-grid">
        <label class="uk-form-label uk-width-2-10" for="width">
                <?php echo JText::_('WF_TABLE_WIDTH'); ?></label>
        <div class="uk-form-controls uk-width-3-10">
            <input id="width" type="text" value="" />
        </div>

        <label class="uk-form-label uk-width-2-10" for="height">
                <?php echo JText::_('WF_TABLE_HEIGHT'); ?></label>
        <div class="uk-form-controls uk-width-3-10">
            <input id="height" type="text" value="" />
        </div>
    </div>