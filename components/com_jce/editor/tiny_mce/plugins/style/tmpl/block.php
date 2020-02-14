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
        <label for="block_wordspacing" class="uk-form-label uk-width-2-10"><?php echo JText::_('WF_STYLES_BLOCK_WORDSPACING'); ?></label>
            <div class="uk-form-controls uk-width-5-10 uk-datalist">
              <select id="block_wordspacing"></select>
            </div>
            <div class="uk-form-controls uk-width-3-10">
              <select id="block_wordspacing_measurement" ></select>
            </div>
      </div>

      <div class="uk-grid uk-grid-small">
        <label for="block_letterspacing" class="uk-form-label uk-width-2-10"><?php echo JText::_('WF_STYLES_BLOCK_LETTERSPACING'); ?></label>
            <div class="uk-form-controls uk-width-5-10 uk-datalist">
              <select id="block_letterspacing"></select>
            </div>
            <div class="uk-form-controls uk-width-3-10">
              <select id="block_letterspacing_measurement"></select>
            </div>
      </div>

      <div class="uk-grid uk-grid-small">
        <label for="block_vertical_alignment" class="uk-form-label uk-width-2-10"><?php echo JText::_('WF_STYLES_BLOCK_VERTICAL_ALIGNMENT'); ?></label>
        <div class="uk-form-controls uk-width-5-10 uk-datalist">
          <select id="block_vertical_alignment"></select>
        </div>
      </div>

      <div class="uk-grid uk-grid-small">
        <label for="block_text_align" class="uk-form-label uk-width-2-10"><?php echo JText::_('WF_STYLES_BLOCK_TEXT_ALIGN'); ?></label>
        <div class="uk-form-controls uk-width-5-10 uk-datalist">
          <select id="block_text_align"></select>
        </div>
      </div>

      <div class="uk-grid uk-grid-small">
        <label for="block_text_indent" class="uk-form-label uk-width-2-10"><?php echo JText::_('WF_STYLES_BLOCK_TEXT_INDENT'); ?></label>
            <div class="uk-form-controls uk-width-2-10">
              <input type="number" id="block_text_indent" />
            </div>
            <div class="uk-form-controls uk-width-2-10">
              <select id="block_text_indent_measurement"></select>
            </div>
      </div>

      <div class="uk-grid uk-grid-small">
        <label for="block_whitespace" class="uk-form-label uk-width-2-10"><?php echo JText::_('WF_STYLES_BLOCK_WHITESPACE'); ?></label>
        <div class="uk-form-controls uk-width-5-10 uk-datalist">
          <select id="block_whitespace"></select>
        </div>
      </div>

      <div class="uk-grid uk-grid-small">
        <label for="block_display" class="uk-form-label uk-width-2-10"><?php echo JText::_('WF_STYLES_BLOCK_DISPLAY'); ?></label>
        <div class="uk-form-controls uk-width-5-10 uk-datalist">
          <select id="block_display"></select>
        </div>
      </div>
