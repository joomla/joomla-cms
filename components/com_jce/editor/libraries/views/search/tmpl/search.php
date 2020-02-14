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
<div id="search-browser" class="uk-width-1-1">
    <div class="uk-grid uk-grid-collapse">
        <div id="searchbox" class="uk-form-icon uk-form-icon-flip uk-width-3-4">
            <input type="text" id="search-input" class="uk-width-1-1" aria-label="<?php echo JText::_('WF_LABEL_SEARCH'); ?>" placeholder="<?php echo JText::_('WF_LABEL_SEARCH'); ?>..." />
            <i class="uk-icon uk-icon-close" id="search-clear"></i>
            <i class="uk-icon uk-icon-spinner"></i>
        </div>

        <div class="uk-button-group uk-width-1-4">
            <button class="uk-button uk-width-2-3 uk-width-mini-1-2" id="search-button"><label class="uk-form-label"><?php echo JText::_('WF_LABEL_SEARCH'); ?></label></button>
            <button class="uk-button uk-width-1-3 uk-width-mini-1-2" id="search-options-button" title="<?php echo JText::_('WF_LABEL_SEARCH_OPTIONS'); ?>" aria-label="<?php echo JText::_('WF_LABEL_SEARCH_OPTIONS'); ?>" aria-haspopup="true"><i class="uk-icon uk-icon-cog"></i></button>
        </div>
    </div>

    <div id="search-options" class="uk-dropdown uk-width-1-1">
        <fieldset class="phrases">
            <legend><?php echo JText::_('WF_SEARCH_FOR'); ?>
            </legend>
            <div class="phrases-box">
                <?php echo $this->lists['searchphrase']; ?>
            </div>
            <div class="ordering-box">
                <label for="ordering" class="ordering">
                    <?php echo JText::_('WF_SEARCH_ORDERING'); ?>
                </label>
                <?php echo $this->lists['ordering']; ?>
            </div>
        </fieldset>
        <fieldset class="search_only">
            <legend><?php echo JText::_('WF_SEARCH_SEARCH_ONLY'); ?></legend>
            <ul>
            <?php
            foreach ($this->searchareas as $val => $txt) :
                ?>
                <li>
                    <input type="checkbox" name="areas[]" value="<?php echo $val; ?>" id="area-<?php echo $val; ?>" />
                <label for="area-<?php echo $val; ?>">
                    <?php echo JText::_($txt); ?>
                </label>
                </li>
            <?php endforeach; ?>
            </ul>
        </fieldset>
    </div>

    <div id="search-result" class="uk-dropdown uk-padding-remove"></div>
</div>
