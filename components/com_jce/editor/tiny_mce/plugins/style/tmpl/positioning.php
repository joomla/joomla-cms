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

  <div class="uk-grid uk-grid-small">
    <label for="positioning_type" class="uk-form-label uk-width-2-10"><?php echo JText::_('WF_STYLES_POSITIONING_TYPE'); ?></label>
    <div class="uk-form-controls uk-width-3-10 uk-datalist">
      <select id="positioning_type"></select>
    </div>
    <label for="positioning_visibility" class="uk-form-label uk-width-2-10"><?php echo JText::_('WF_STYLES_VISIBILITY'); ?></label>
    <div class="uk-form-controls uk-width-3-10 uk-datalist">
      <select id="positioning_visibility"></select>
    </div>
  </div>

  <div class="uk-grid uk-grid-small">
    <label for="positioning_width" class="uk-form-label uk-width-2-10"><?php echo JText::_('WF_STYLES_WIDTH'); ?></label>
        <div class="uk-form-controls uk-width-2-10">
          <input type="number" id="positioning_width" onchange="StyleDialog.synch('positioning_width','box_width');" />
        </div>
        <div class="uk-form-controls uk-width-2-10">
          <select id="positioning_width_measurement" ></select>
        </div>
        <label for="positioning_zindex" class="uk-form-label uk-width-2-10"><?php echo JText::_('WF_STYLES_ZINDEX'); ?></label>
        <div class="uk-form-controls uk-width-2-10">
          <input type="number" id="positioning_zindex" />
        </div>
  </div>

  <div class="uk-grid uk-grid-small">
    <label for="positioning_height" class="uk-form-label uk-width-2-10"><?php echo JText::_('WF_STYLES_HEIGHT'); ?></label>
        <div class="uk-form-controls uk-width-2-10">
          <input type="number" id="positioning_height" onchange="StyleDialog.synch('positioning_height','box_height');" />
        </div>
        <div class="uk-form-controls uk-width-2-10">
          <select id="positioning_height_measurement" ></select>
        </div>
        <label for="positioning_overflow" class="uk-form-label uk-width-2-10"><?php echo JText::_('WF_STYLES_OVERFLOW'); ?></label>
        <div class="uk-form-controls uk-width-2-10 uk-datalist">
          <select id="positioning_overflow"></select>
        </div>
  </div>

<div class="uk-grid uk-grid-small">
<div class="uk-width-5-10">
  <fieldset>
    <legend><?php echo JText::_('WF_STYLES_PLACEMENT'); ?></legend>


      <div class="uk-form-row">
        <input type="checkbox" id="positioning_placement_same" checked="checked" onclick="StyleDialog.toggleSame(this,'positioning_placement');" />
        <label for="positioning_placement_same"><?php echo JText::_('WF_STYLES_SAME'); ?></label>
      </div>
      <div class="uk-grid uk-grid-small">
        <label for="positioning_placement_left" class="uk-form-label uk-width-2-10"><?php echo JText::_('WF_STYLES_TOP'); ?></label>


            <div class="uk-form-controls uk-width-4-10">
              <input type="number" id="positioning_placement_top" />
            </div>
            <div class="uk-form-controls uk-width-4-10">
              <select id="positioning_placement_top_measurement" ></select>
            </div>


      </div>
      <div class="uk-grid uk-grid-small">
        <label for="positioning_placement_left" class="uk-form-label uk-width-2-10"><?php echo JText::_('WF_STYLES_RIGHT'); ?></label>


            <div class="uk-form-controls uk-width-4-10">
              <input type="number" id="positioning_placement_right" disabled="disabled" />
            </div>
            <div class="uk-form-controls uk-width-4-10">
              <select id="positioning_placement_right_measurement" disabled="disabled"></select>
            </div>


      </div>
      <div class="uk-grid uk-grid-small">
        <label for="positioning_placement_left" class="uk-form-label uk-width-2-10"><?php echo JText::_('WF_STYLES_BOTTOM'); ?></label>


            <div class="uk-form-controls uk-width-4-10">
              <input type="number" id="positioning_placement_bottom" disabled="disabled" />
            </div>
            <div class="uk-form-controls uk-width-4-10">
              <select id="positioning_placement_bottom_measurement" disabled="disabled"></select>
            </div>


      </div>
      <div class="uk-grid uk-grid-small">
        <label for="positioning_placement_left" class="uk-form-label uk-width-2-10"><?php echo JText::_('WF_STYLES_LEFT'); ?></label>


            <div class="uk-form-controls uk-width-4-10">
              <input type="number" id="positioning_placement_left" disabled="disabled" />
            </div>
            <div class="uk-form-controls uk-width-4-10">
              <select id="positioning_placement_left_measurement" disabled="disabled"></select>
            </div>


      </div>

  </fieldset>
</div>

<div class="uk-width-5-10">
  <fieldset>
    <legend><?php echo JText::_('WF_STYLES_CLIP'); ?></legend>


      <div class="uk-form-row">
        <input type="checkbox" id="positioning_clip_same" checked="checked" onclick="StyleDialog.toggleSame(this,'positioning_clip');" />
        <label for="positioning_clip_same"><?php echo JText::_('WF_STYLES_SAME'); ?></label>
      </div>
      <div class="uk-grid uk-grid-small">
        <label for="positioning_clip_top" class="uk-form-label uk-width-2-10"><?php echo JText::_('WF_STYLES_TOP'); ?></label>


            <div class="uk-form-controls uk-width-4-10">
              <input type="number" id="positioning_clip_top" />
            </div>
            <div class="uk-form-controls uk-width-4-10">
              <select id="positioning_clip_top_measurement" ></select>
            </div>


      </div>
      <div class="uk-grid uk-grid-small">
        <label for="positioning_clip_right" class="uk-form-label uk-width-2-10"><?php echo JText::_('WF_STYLES_RIGHT'); ?></label>


            <div class="uk-form-controls uk-width-4-10">
              <input type="number" id="positioning_clip_right" disabled="disabled" />
            </div>
            <div class="uk-form-controls uk-width-4-10">
              <select id="positioning_clip_right_measurement" disabled="disabled"></select>
            </div>


      </div>
      <div class="uk-grid uk-grid-small">
        <label for="positioning_clip_bottom" class="uk-form-label uk-width-2-10"><?php echo JText::_('WF_STYLES_BOTTOM'); ?></label>


            <div class="uk-form-controls uk-width-4-10">
              <input type="number" id="positioning_clip_bottom" disabled="disabled" />
            </div>
            <div class="uk-form-controls uk-width-4-10">
              <select id="positioning_clip_bottom_measurement" disabled="disabled"></select>
            </div>


      </div>
      <div class="uk-grid uk-grid-small">
        <label for="positioning_clip_left" class="uk-form-label uk-width-2-10"><?php echo JText::_('WF_STYLES_LEFT'); ?></label>

            <div class="uk-form-controls uk-width-4-10">
              <input type="number" id="positioning_clip_left" disabled="disabled" />
            </div>
            <div class="uk-form-controls uk-width-4-10">
              <select id="positioning_clip_left_measurement" disabled="disabled"></select>
            </div>


      </div>

  </fieldset>
</div>
</div>
