<?php
/**
 * @copyright    Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license    GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('_WF_EXT') or die('RESTRICTED');
?>
<div class="uk-form-row">
    <label for="dailymotion_autoPlay" title="<?php echo JText::_('WF_AGGREGATOR_DAILYMOTION_AUTOPLAY_DESC') ?>"
           class="tooltip uk-form-label uk-width-1-5"><?php echo JText::_('WF_AGGREGATOR_DAILYMOTION_AUTOPLAY') ?></label>
    <div class="uk-width-4-5">
        <div class="uk-form-controls uk-width-1-5">
            <input type="checkbox" id="dailymotion_autoPlay" />
        </div>

        <label for="dailymotion_start" title="<?php echo JText::_('WF_AGGREGATOR_DAILYMOTION_START_DESC') ?>"
               class="tooltip uk-form-label uk-width-1-5"><?php echo JText::_('WF_AGGREGATOR_DAILYMOTION_START') ?></label>
        <div class="uk-form-controls uk-width-1-5">
            <input id="dailymotion_start" type="number" value="" />
        </div>
    </div>
</div>
<div class="uk-form-row">
    <label class="uk-form-label uk-width-1-5"
           title="<?php echo JText::_('WF_AGGREGATOR_DAILYMOTION_SIZE'); ?>"><?php echo JText::_('WF_AGGREGATOR_DAILYMOTION_SIZE'); ?></label>

    <div class="uk-form-controls uk-width-4-5">
        <select id="dailymotion_player_size">
            <option value="320"><?php echo JText::_('WF_AGGREGATOR_DAILYMOTION_SIZE_SMALL'); ?></option>
            <option value="480"><?php echo JText::_('WF_AGGREGATOR_DAILYMOTION_SIZE_MEDIUM'); ?></option>
            <option value="560"><?php echo JText::_('WF_AGGREGATOR_DAILYMOTION_SIZE_LARGE'); ?></option>
            <option value=""><?php echo JText::_('WF_AGGREGATOR_DAILYMOTION_SIZE_CUSTOM'); ?></option>
        </select>

        <input type="number" id="dailymotion_player_size_custom" class="uk-hidden uk-margin-small-left" />
    </div>
</div>
