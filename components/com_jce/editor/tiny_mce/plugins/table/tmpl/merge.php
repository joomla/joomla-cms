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
<div class="uk-grid uk-grid-small">
    <label class="uk-form-label uk-width-7-10"><?php echo JText::_('WF_TABLE_COLS'); ?>:</label>
    <div class="uk-form-controls uk-width-3-10">
        <input type="number" min="1" id="numcols" value=""/>
    </div>
    <label class="uk-form-label uk-width-7-10"><?php echo JText::_('WF_TABLE_ROWS'); ?>:</label>
    <div class="uk-form-controls uk-width-3-10">
        <input type="number" min="1" id="numrows" value=""/>
    </div>
</div>
