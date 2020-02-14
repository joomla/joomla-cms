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
  <div class="uk-form-row">
    <label class="uk-form-label uk-width-2-10" for="contenteditable">
      <?php echo JText::_('WF_LABEL_CONTENTEDITBALE'); ?>
    </label>
    <div class="uk-form-controls uk-width-8-10">
      <select id="contenteditable">
        <option value="">
          <?php echo JText::_('WF_OPTION_NOT_SET'); ?>
        </option>
        <option value="true">
          <?php echo JText::_('JYES'); ?>
        </option>
        <option value="false">
          <?php echo JText::_('JNO'); ?>
        </option>
        <option value="inherit">
          <?php echo JText::_('WF_OPTION_INHERIT'); ?>
        </option>
      </select>
    </div>
  </div>

  <div class="uk-form-row">
    <label class="uk-form-label uk-width-2-10" for="draggable">
      <?php echo JText::_('WF_LABEL_DRAGGABLE'); ?>
    </label>
    <div class="uk-form-controls uk-width-8-10">
      <select id="draggable">
        <option value="">
          <?php echo JText::_('WF_OPTION_NOT_SET'); ?>
        </option>
        <option value="true">
          <?php echo JText::_('JYES'); ?>
        </option>
        <option value="false">
          <?php echo JText::_('JNO'); ?>
        </option>
        <option value="auto">
          <?php echo JText::_('WF_OPTION_AUTO'); ?>
        </option>
      </select>
    </div>
  </div>

  <div class="uk-form-row">
    <label class="uk-form-label uk-width-2-10" for="hidden">
      <?php echo JText::_('WF_LABEL_HIDDEN'); ?>
    </label>
    <div class="uk-form-controls uk-width-8-10">
      <select id="hidden">
        <option value="">
          <?php echo JText::_('WF_OPTION_NOT_SET'); ?>
        </option>
        <option value="">
          <?php echo JText::_('JNO'); ?>
        </option>
        <option value="hidden">
          <?php echo JText::_('JYES'); ?>
        </option>
      </select>
    </div>
  </div>

  <div class="uk-form-row">
    <label class="uk-form-label uk-width-2-10" for="spellcheck">
      <?php echo JText::_('WF_LABEL_SPELLCHECK'); ?>
    </label>
    <div class="uk-form-controls uk-width-8-10">
      <select id="spellcheck">
        <option value="">
          <?php echo JText::_('WF_OPTION_NOT_SET'); ?>
        </option>
        <option value="true">
          <?php echo JText::_('JYES'); ?>
        </option>
        <option value="false">
          <?php echo JText::_('JNO'); ?>
        </option>
      </select>
    </div>
  </div>

  <div class="uk-form-row">
    <label for="custom_attributes" class="uk-form-label uk-width-2-10">
      <?php echo JText::_('WF_LABEL_OTHER'); ?>
    </label>
    <div class="uk-form-controls uk-width-8-10">
      <div class="uk-repeatable">
        <div class="uk-form-controls uk-grid uk-grid-small uk-margin-small uk-width-9-10">
          <label class="uk-form-label uk-width-1-10">
            <?php echo JText::_('WF_LABEL_NAME'); ?>
          </label>
          <div class="uk-form-controls uk-width-4-10">
            <input type="text" name="custom_name[]" />
          </div>
          <label class="uk-form-label uk-width-1-10">
            <?php echo JText::_('WF_LABEL_VALUE'); ?>
          </label>
          <div class="uk-form-controls uk-width-4-10">
            <input type="text" name="custom_value[]" />
          </div>
        </div>
        <div class="uk-form-controls uk-width-1-10 uk-margin-small-left">
          <button type="button" class="uk-button uk-button-link uk-repeatable-create">
            <i class="uk-icon-plus"></i>
          </button>
          <button type="button" class="uk-button uk-button-link uk-repeatable-delete">
            <i class="uk-icon-trash"></i>
          </button>
        </div>
      </div>
    </div>
  </div>