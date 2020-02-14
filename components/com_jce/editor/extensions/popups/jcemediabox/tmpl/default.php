<?php

/**
 * @copyright     Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('_WF_EXT') or die('RESTRICTED');

?>
    <div class="uk-form-row">
        <label for="jcemediabox_popup_title" class="uk-form-label uk-width-1-5 hastip" title="<?php echo JText::_('WF_POPUPS_JCEMEDIABOX_OPTION_TITLE_DESC'); ?>"><?php echo JText::_('WF_POPUPS_JCEMEDIABOX_OPTION_TITLE'); ?></label>
        <div class="uk-form-controls uk-width-4-5">
          <input id="jcemediabox_popup_title" class="uk-input-multiple" type="text" class="text" value="" />
        </div>
    </div>

    <div class="uk-form-row">
        <label for="jcemediabox_popup_caption" class="uk-form-label uk-width-1-5 hastip" title="<?php echo JText::_('WF_POPUPS_JCEMEDIABOX_CAPTION_DESC'); ?>"><?php echo JText::_('WF_POPUPS_JCEMEDIABOX_CAPTION'); ?></label>
        <div class="uk-form-controls uk-width-4-5">
          <input id="jcemediabox_popup_caption" class="uk-input-multiple" type="text" class="text" value="" />
        </div>
    </div>

    <div class="uk-form-row">
        <label for="jcemediabox_popup_group" class="uk-form-label uk-width-1-5 hastip" title="<?php echo JText::_('WF_POPUPS_JCEMEDIABOX_GROUP_DESC'); ?>"><?php echo JText::_('WF_POPUPS_JCEMEDIABOX_GROUP'); ?></label>
        <div class="uk-form-controls uk-width-4-5">
          <input id="jcemediabox_popup_group" type="text" class="text" value="" />
        </div>
    </div>

    <div class="uk-form-row">
            <label class="hastip uk-form-label uk-width-1-5" title="<?php echo JText::_('WF_LABEL_DIMENSIONS_DESC'); ?>">
                <?php echo JText::_('WF_LABEL_DIMENSIONS'); ?>
            </label>
            <div class="uk-form-controls uk-width-4-5 uk-form-constrain">

                <div class="uk-form-controls">
                    <input type="text" id="jcemediabox_popup_width" value="" class="uk-text-muted" />
                </div>

                <div class="uk-form-controls">
                    <strong class="uk-form-label uk-text-center uk-vertical-align-middle">&times;</strong>
                </div>

                <div class="uk-form-controls">
                    <input type="text" id="jcemediabox_popup_height" value="" class="uk-text-muted" />
                </div>

                <label class="uk-form-label">
                    <input class="uk-constrain-checkbox" type="checkbox" checked />
                    <?php echo JText::_('WF_LABEL_PROPORTIONAL'); ?>
                </label>
            </div>
        </div>

    <div class="uk-form-row">
        <label for="jcemediabox_popup_icon" class="uk-form-label uk-width-1-5 hastip" title="<?php echo JText::_('WF_POPUPS_JCEMEDIABOX_ICON_DESC'); ?>"><?php echo JText::_('WF_POPUPS_JCEMEDIABOX_ICON'); ?></label>
        <div class="uk-form-controls uk-width-4-5">
            <div class="uk-width-1-5">
              <select id="jcemediabox_popup_icon">
                  <option value="0"><?php echo JText::_('JNO'); ?></option>
                  <option value="1" selected="selected"><?php echo JText::_('JYES'); ?></option>
              </select>
            </div>
            <div class="uk-width-3-5 uk-margin-left">
              <label for="jcemediabox_popup_icon_position" class="uk-form-label uk-width-2-5 hastip" title="<?php echo JText::_('WF_POPUPS_JCEMEDIABOX_ICON_POSITION_DESC'); ?>"><?php echo JText::_('WF_POPUPS_JCEMEDIABOX_ICON_POSITION'); ?></label>
              <div class="uk-form-controls uk-width-3-5">
                <select id="jcemediabox_popup_icon_position">
                    <option value=""><?php echo JText::_('WF_OPTION_NOT_SET'); ?></option>
                    <option value="zoom-left"><?php echo JText::_('WF_OPTION_LEFT'); ?></option>
                    <option value="zoom-right"><?php echo JText::_('WF_OPTION_RIGHT'); ?></option>
                    <option value="zoom-top-left"><?php echo JText::_('WF_OPTION_TOP_LEFT'); ?></option>
                    <option value="zoom-top-right"><?php echo JText::_('WF_OPTION_TOP_RIGHT'); ?></option>
                    <option value="zoom-bottom-left"><?php echo JText::_('WF_OPTION_BOTTOM_LEFT'); ?></option>
                    <option value="zoom-bottom-right"><?php echo JText::_('WF_OPTION_BOTTOM_RIGHT'); ?></option>
                </select>
              </div>
            </div>
        </div>
    </div>

    <div class="uk-form-row">
        <label for="jcemediabox_popup_hide" class="uk-form-label uk-width-1-5 hastip" title="<?php echo JText::_('WF_POPUPS_JCEMEDIABOX_HIDE_DESC'); ?>"><?php echo JText::_('WF_POPUPS_JCEMEDIABOX_HIDE'); ?></label>
        <div class="uk-form-controls uk-width-4-5">
            <div class="uk-width-1-5">
                <select id="jcemediabox_popup_hide">
                      <option value="0"><?php echo JText::_('JNO'); ?></option>
                      <option value="1"><?php echo JText::_('JYES'); ?></option>
                </select>
            </div>
            <div class="uk-width-3-5 uk-margin-left">
                <label for="jcemediabox_popup_autopopup" class="uk-form-label uk-width-2-5 hastip" title="<?php echo JText::_('WF_POPUPS_JCEMEDIABOX_AUTO_DESC'); ?>"><?php echo JText::_('WF_POPUPS_JCEMEDIABOX_AUTO'); ?></label>
                <div class="uk-form-controls uk-width-3-5">
                    <select id="jcemediabox_popup_autopopup">
                        <option value=""><?php echo JText::_('WF_OPTION_NOT_SET'); ?></option>
                        <option value="autopopup-single"><?php echo JText::_('WF_POPUPS_JCEMEDIABOX_AUTO_SINGLE'); ?></option>
                        <option value="autopopup-multiple"><?php echo JText::_('WF_POPUPS_JCEMEDIABOX_AUTO_MULTIPLE'); ?></option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="uk-form-row">
        <label for="jcemediabox_popup_mediatype" class="uk-form-label uk-width-1-5 hastip" title="<?php echo JText::_('WF_POPUPS_JCEMEDIABOX_MEDIATYPE_DESC'); ?>"><?php echo JText::_('WF_POPUPS_JCEMEDIABOX_MEDIATYPE'); ?></label>
        <div class="uk-form-controls uk-width-2-5">
          <select id="jcemediabox_popup_mediatype">
                <option value=""><?php echo JText::_('WF_OPTION_NOT_SET'); ?></option>
                <option value="text/html"><?php echo JText::_('WF_POPUPS_JCEMEDIABOX_INTERNAL'); ?></option>
                <option value="iframe"><?php echo JText::_('WF_POPUPS_JCEMEDIABOX_EXTERNAL'); ?></option>
                <option value="image"><?php echo JText::_('WF_POPUPS_JCEMEDIABOX_IMAGE'); ?></option>
                <option value="video/youtube"><?php echo JText::_('WF_POPUPS_JCEMEDIABOX_YOUTUBE'); ?></option>
                <option value="video/vimeo"><?php echo JText::_('WF_POPUPS_JCEMEDIABOX_VIMEO'); ?></option>
                <option value="application/x-shockwave-flash"><?php echo JText::_('WF_POPUPS_JCEMEDIABOX_FLASH'); ?></option>
                <option value="video/quicktime"><?php echo JText::_('WF_POPUPS_JCEMEDIABOX_QUICKTIME'); ?></option>
                <option value="application/x-mplayer2"><?php echo JText::_('WF_POPUPS_JCEMEDIABOX_WINDOWSMEDIA'); ?></option>
                <option value="video/divx"><?php echo JText::_('WF_POPUPS_JCEMEDIABOX_DIVX'); ?></option>
                <option value="application/x-director"><?php echo JText::_('WF_POPUPS_JCEMEDIABOX_DIRECTOR'); ?></option>
                <option value="audio/x-pn-realaudio-plugin"><?php echo JText::_('WF_POPUPS_JCEMEDIABOX_REAL'); ?></option>
                <option value="video/mp4"><?php echo JText::_('WF_POPUPS_JCEMEDIABOX_VIDEO_MP4'); ?></option>
                <option value="audio/mp3"><?php echo JText::_('WF_POPUPS_JCEMEDIABOX_AUDIO_MP3'); ?></option>
                <option value="video/webm"><?php echo JText::_('WF_POPUPS_JCEMEDIABOX_VIDEO_WEBM'); ?></option>
                <option value="audio/webm"><?php echo JText::_('WF_POPUPS_JCEMEDIABOX_AUDIO_WEBM'); ?></option>
            </select>
        </div>
    </div>
    <div class="uk-form-row">
        <label for="jcemediabox_popup_params" class="uk-form-label uk-width-1-5 hastip" title="<?php echo JText::_('WF_POPUPS_JCEMEDIABOX_PARAMS_DESC'); ?>"><?php echo JText::_('WF_POPUPS_JCEMEDIABOX_PARAMS'); ?></label>
        <div class="uk-width-4-5" id="jcemediabox_popup_params">
          <div class="uk-form-row uk-repeatable">
                  <div class="uk-form-controls uk-grid uk-grid-small uk-width-8-10">
                      <label class="uk-form-label uk-width-1-10"><?php echo JText::_('WF_LABEL_NAME'); ?></label>
                      <div class="uk-form-controls uk-width-3-10">
                        <input type="text" name="jcemediabox_popup_params_name[]" />
                      </div>
                      <label class="uk-form-label uk-width-1-10"><?php echo JText::_('WF_LABEL_VALUE'); ?></label>
                      <div class="uk-form-controls uk-width-5-10">
                        <input type="text" name="jcemediabox_popup_params_value[]" />
                      </div>
                  </div>
                  <div class="uk-form-controls uk-width-1-10 uk-margin-small-left">
                    <button class="uk-button uk-button-link uk-repeatable-create" aria-label="<?php echo JText::_('WF_LABEL_ADD'); ?>" title="<?php echo JText::_('WF_LABEL_ADD'); ?>"><i class="uk-icon-plus"></i></button>
                    <button class="uk-button uk-button-link uk-repeatable-delete" aria-label="<?php echo JText::_('WF_LABEL_REMOVE'); ?>" title="<?php echo JText::_('WF_LABEL_REMOVE'); ?>"><i class="uk-icon-trash"></i></button>
                  </div>
          </div>
        </div>
    </div>
