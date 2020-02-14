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
  <div class="uk-width-3-10">
    <fieldset>
      <legend><?php echo JText::_('WF_STYLES_STYLE'); ?></legend>
      <div class="uk-form-row">
        <input type="checkbox" id="border_style_same" checked="checked" onclick="StyleDialog.toggleSame(this,'border_style');" />
        <label for="border_style_same"><?php echo JText::_('WF_STYLES_SAME'); ?></label>
      </div>
    <div class="uk-grid uk-grid-small">
      <label for="border_style_top" class="uk-form-label uk-width-3-10"><?php echo JText::_('WF_STYLES_TOP'); ?></label>
      <div class="uk-form-controls uk-width-7-10 uk-datalist">
        <select id="border_style_top"></select>
      </div>
    </div>

    <div class="uk-grid uk-grid-small">
      <label for="border_style_right" class="uk-form-label uk-width-3-10"><?php echo JText::_('WF_STYLES_RIGHT'); ?></label>
      <div class="uk-form-controls uk-width-7-10 uk-datalist">
        <select id="border_style_right"></select>
      </div>
    </div>

    <div class="uk-grid uk-grid-small">
      <label for="border_style_bottom" class="uk-form-label uk-width-3-10"><?php echo JText::_('WF_STYLES_BOTTOM'); ?></label>
      <div class="uk-form-controls uk-width-7-10 uk-datalist">
        <select id="border_style_bottom"></select>
      </div>
    </div>

    <div class="uk-grid uk-grid-small">
      <label for="border_style_left" class="uk-form-label uk-width-3-10"><?php echo JText::_('WF_STYLES_LEFT'); ?></label>
      <div class="uk-form-controls uk-width-7-10 uk-datalist">
        <select id="border_style_left"></select>
      </div>
    </div>
  </fieldset>
  </div>
  <div class="uk-width-4-10">
    <fieldset>
      <legend><?php echo JText::_('WF_STYLES_WIDTH'); ?></legend>
      <div class="uk-form-row">
        <input type="checkbox" id="border_width_same" checked="checked" onclick="StyleDialog.toggleSame(this,'border_width');" />
        <label for="border_width_same"><?php echo JText::_('WF_STYLES_SAME'); ?></label>
      </div>

      <div class="uk-grid uk-grid-small">
        <div class="uk-form-controls uk-width-5-10 uk-datalist">
          <select id="border_width_top"></select>
        </div>
        <div class="uk-form-controls uk-width-5-10">
          <select id="border_width_top_measurement" ></select>
        </div>
      </div>

      <div class="uk-grid uk-grid-small">
        <div class="uk-form-controls uk-width-5-10 uk-datalist">
          <select id="border_width_right"></select>
        </div>
        <div class="uk-form-controls uk-width-5-10">
          <select id="border_width_right_measurement" ></select>
        </div>
      </div>

      <div class="uk-grid uk-grid-small">
        <div class="uk-form-controls uk-width-5-10 uk-datalist">
          <select id="border_width_bottom"></select>
        </div>
        <div class="uk-form-controls uk-width-5-10">
          <select id="border_width_bottom_measurement" ></select>
        </div>
      </div>

      <div class="uk-grid uk-grid-small">
        <div class="uk-form-controls uk-width-5-10 uk-datalist">
          <select id="border_width_left"></select>
        </div>
        <div class="uk-form-controls uk-width-5-10">
          <select id="border_width_left_measurement" ></select>
        </div>
      </div>
    </fieldset>
  </div>
  <div class="uk-width-3-10">
    <fieldset>
      <legend><?php echo JText::_('WF_STYLES_COLOR'); ?></legend>
      <div class="uk-form-row">
        <input type="checkbox" id="border_color_same" checked="checked" onclick="StyleDialog.toggleSame(this,'border_color');" />
        <label for="border_color_same"><?php echo JText::_('WF_STYLES_SAME'); ?></label>
      </div>

      <div class="uk-form-row">
        <div class="uk-form-controls uk-width-2-3">
          <input id="border_color_top" class="color" type="text" value="" />
        </div>
      </div>

      <div class="uk-form-row">
        <div class="uk-form-controls uk-width-2-3">
          <input id="border_color_right" class="color" type="text" value="" />
        </div>
      </div>

      <div class="uk-form-row">
        <div class="uk-form-controls uk-width-2-3">
          <input id="border_color_bottom" class="color" type="text" value="" />
        </div>
      </div>

      <div class="uk-form-row">
        <div class="uk-form-controls uk-width-2-3">
          <input id="border_color_left" class="color" type="text" value="" />
        </div>
      </div>
    </fieldset>
  </div>
</div>
