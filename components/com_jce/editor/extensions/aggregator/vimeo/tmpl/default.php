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
    <label for="vimeo_color" title="<?php echo JText::_('WF_AGGREGATOR_VIMEO_COLOR_DESC') ?>"
           class="tooltip uk-form-label uk-width-1-5"><?php echo JText::_('WF_AGGREGATOR_VIMEO_COLOR') ?></label>

    <div class="uk-form-controls uk-width-1-5">
        <input type="text" id="vimeo_color" class="color"/>
    </div>
</div>

<div class="uk-form-row">

    <label for="vimeo_intro" title="<?php echo JText::_('WF_AGGREGATOR_VIMEO_INTRO_DESC') ?>"
           class="tooltip uk-form-label uk-width-1-5"><?php echo JText::_('WF_AGGREGATOR_VIMEO_INTRO') ?></label>
    <div class="uk-form-controls uk-width-4-5">
        <input type="checkbox" id="vimeo_portrait" />
        <label for="vimeo_portrait" title="<?php echo JText::_('WF_AGGREGATOR_VIMEO_PORTRAIT_DESC') ?>"
               class="tooltip uk-margin-right"><?php echo JText::_('WF_AGGREGATOR_VIMEO_PORTRAIT') ?></label>

        <input type="checkbox" id="vimeo_title" />
        <label for="vimeo_title" title="<?php echo JText::_('WF_AGGREGATOR_VIMEO_INTROTITLE_DESC') ?>"
               class="tooltip uk-margin-right"><?php echo JText::_('WF_AGGREGATOR_VIMEO_INTROTITLE') ?></label>

        <input type="checkbox" id="vimeo_byline" />
        <label for="vimeo_byline" title="<?php echo JText::_('WF_AGGREGATOR_VIMEO_BYLINE_DESC') ?>"
               class="tooltip"><?php echo JText::_('WF_AGGREGATOR_VIMEO_BYLINE') ?></label>
    </div>
</div>

<div class="uk-form-row">
    <label for="vimeo_special"
           class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_AGGREGATOR_VIMEO_SPECIAL') ?></label>
    <div class="uk-form-controls uk-width-4-5">

    <input type="checkbox" id="vimeo_autoplay"/>
    <label for="vimeo_autoplay" title="<?php echo JText::_('WF_AGGREGATOR_VIMEO_AUTOPLAY_DESC') ?>"
           class="tooltip uk-margin-right"><?php echo JText::_('WF_AGGREGATOR_VIMEO_AUTOPLAY') ?></label>

    <input type="checkbox" id="vimeo_loop"/>
    <label for="vimeo_loop" title="<?php echo JText::_('WF_AGGREGATOR_VIMEO_LOOP_DESC') ?>"
           class="tooltip uk-margin-right"><?php echo JText::_('WF_AGGREGATOR_VIMEO_LOOP') ?></label>

    <input type="checkbox" id="vimeo_fullscreen" checked="checked"/>
    <label for="vimeo_fullscreen" title="<?php echo JText::_('WF_AGGREGATOR_VIMEO_FULLSCREEN_DESC') ?>"
           class="tooltip"><?php echo JText::_('WF_AGGREGATOR_VIMEO_FULLSCREEN') ?></label>
        </div>
</div>