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
    <div class="uk-width-4-5">

        <div class="uk-grid uk-grid-small">
            <label for="src" class="hastip uk-form-label uk-width-1-5" title="<?php echo JText::_('WF_LABEL_URL_DESC'); ?>">
                <?php echo JText::_('WF_LABEL_URL'); ?>
            </label>
            <div class="uk-form-controls uk-width-4-5">
                <input type="text" id="src" value="" class="filebrowser" required />
            </div>
        </div>

        <div class="uk-grid uk-grid-small">
            <label for="alt" class="hastip uk-form-label uk-width-1-5" title="<?php echo JText::_('WF_LABEL_ALT_DESC'); ?>">
                <?php echo JText::_('WF_LABEL_ALT'); ?>
            </label>
            <div class="uk-form-controls uk-width-4-5">
                <input type="text" id="alt" value="" />
            </div>
        </div>

        <div class="uk-grid uk-grid-small" id="attributes-dimensions">
            <label class="hastip uk-form-label uk-width-1-5" title="<?php echo JText::_('WF_LABEL_DIMENSIONS_DESC'); ?>">
                <?php echo JText::_('WF_LABEL_DIMENSIONS'); ?>
            </label>
            <div class="uk-form-controls uk-width-4-5 uk-form-constrain">

                <div class="uk-form-controls">
                    <input type="text" id="width" value="" class="uk-text-muted" />
                </div>

                <div class="uk-form-controls">
                    <strong class="uk-form-label uk-text-center uk-vertical-align-middle">&times;</strong>
                </div>

                <div class="uk-form-controls">
                    <input type="text" id="height" value="" class="uk-text-muted" />
                </div>

                <label class="uk-form-label">
                    <input class="uk-constrain-checkbox" type="checkbox" checked />
                    <?php echo JText::_('WF_LABEL_PROPORTIONAL'); ?>
                </label>
            </div>
        </div>

        <div class="uk-grid uk-grid-small" id="attributes-align">
            <label for="align" class="hastip uk-form-label uk-width-1-5"
                   title="<?php echo JText::_('WF_LABEL_ALIGN_DESC'); ?>">
                <?php echo JText::_('WF_LABEL_ALIGN'); ?>
            </label>

            <div class="uk-width-2-5">
                <div class="uk-form-controls uk-width-9-10">
                    <select id="align">
                        <option value=""><?php echo JText::_('WF_OPTION_NOT_SET'); ?></option>
                        <optgroup label="------------">
                            <option value="left"><?php echo JText::_('WF_OPTION_ALIGN_LEFT'); ?></option>
                            <option value="center"><?php echo JText::_('WF_OPTION_ALIGN_CENTER'); ?></option>
                            <option value="right"><?php echo JText::_('WF_OPTION_ALIGN_RIGHT'); ?></option>
                        </optgroup>
                        <optgroup label="------------">
                            <option value="top"><?php echo JText::_('WF_OPTION_ALIGN_TOP'); ?></option>
                            <option value="middle"><?php echo JText::_('WF_OPTION_ALIGN_MIDDLE'); ?></option>
                            <option value="bottom"><?php echo JText::_('WF_OPTION_ALIGN_BOTTOM'); ?></option>
                        </optgroup>
                    </select>
                </div>
            </div>
            <div class="uk-width-2-5">
                <label for="clear" class="hastip uk-form-label uk-width-3-10"
                       title="<?php echo JText::_('WF_LABEL_CLEAR_DESC'); ?>">
                    <?php echo JText::_('WF_LABEL_CLEAR'); ?>
                </label>
                <div class="uk-form-controls uk-width-7-10">
                    <select id="clear" disabled>
                        <option value=""><?php echo JText::_('WF_OPTION_NOT_SET'); ?></option>
                        <option value="none"><?php echo JText::_('WF_OPTION_CLEAR_NONE'); ?></option>
                        <option value="both"><?php echo JText::_('WF_OPTION_CLEAR_BOTH'); ?></option>
                        <option value="left"><?php echo JText::_('WF_OPTION_CLEAR_LEFT'); ?></option>
                        <option value="right"><?php echo JText::_('WF_OPTION_CLEAR_RIGHT'); ?></option>
                    </select>
                </div>
            </div>
        </div>

        <div class="uk-hidden-mini uk-grid uk-grid-small" id="attributes-margin">
            <label for="margin" class="hastip uk-form-label uk-width-1-5" title="<?php echo JText::_('WF_LABEL_MARGIN_DESC'); ?>">
                <?php echo JText::_('WF_LABEL_MARGIN'); ?>
            </label>
            <div class="uk-form-controls uk-width-4-5 uk-grid uk-grid-small uk-form-equalize">

              <label for="margin_top" class="uk-form-label">
                  <?php echo JText::_('WF_OPTION_TOP'); ?>
              </label>
              <div class="uk-form-controls">
                  <input type="text" id="margin_top" value="" />
              </div>

                    <label for="margin_right" class="uk-form-label">
                        <?php echo JText::_('WF_OPTION_RIGHT'); ?>
                    </label>
                    <div class="uk-form-controls">
                        <input type="text" id="margin_right" value="" />
                    </div>

                    <label for="margin_bottom" class="uk-form-label">
                        <?php echo JText::_('WF_OPTION_BOTTOM'); ?>
                    </label>
                    <div class="uk-form-controls">
                        <input type="text" id="margin_bottom" value="" />
                    </div>

                    <label for="margin_left" class="uk-form-label">
                        <?php echo JText::_('WF_OPTION_LEFT'); ?>
                    </label>
                    <div class="uk-form-controls">
                        <input type="text" id="margin_left" value="" />
                    </div>
                    <label class="uk-form-label">
                        <input type="checkbox" class="uk-equalize-checkbox" />
                        <?php echo JText::_('WF_LABEL_EQUAL'); ?>
                    </label>
            </div>
        </div>

        <div class="uk-hidden-mini uk-grid uk-grid-small" id="attributes-border">
            <label for="border" class="hastip uk-form-label uk-width-1-5" title="<?php echo JText::_('WF_LABEL_BORDER_DESC'); ?>">
                <?php echo JText::_('WF_LABEL_BORDER'); ?>
            </label>

            <div class="uk-form-controls uk-width-4-5">
                <div class="uk-form-controls uk-width-0-3 uk-margin-small-top">
                    <input type="checkbox" id="border" />
                </div>

                <label for="border_width" class="hastip uk-form-label uk-width-1-10 uk-margin-small-left"
                       title="<?php echo JText::_('WF_LABEL_BORDER_WIDTH_DESC'); ?>"><?php echo JText::_('WF_LABEL_WIDTH'); ?></label>
                <div class="uk-form-controls uk-width-2-10 uk-datalist">
                    <select pattern="[0-9]+" id="border_width">
                        <option value="inherit"><?php echo JText::_('WF_OPTION_NOT_SET'); ?></option>
                        <option value="0">0</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                        <option value="7">7</option>
                        <option value="8">8</option>
                        <option value="9">9</option>
                        <option value="thin"><?php echo JText::_('WF_OPTION_BORDER_THIN'); ?></option>
                        <option value="medium"><?php echo JText::_('WF_OPTION_BORDER_MEDIUM'); ?></option>
                        <option value="thick"><?php echo JText::_('WF_OPTION_BORDER_THICK'); ?></option>
                    </select>
                </div>

                <label for="border_style" class="hastip uk-form-label uk-width-1-10 uk-margin-small-left"
                       title="<?php echo JText::_('WF_LABEL_BORDER_STYLE_DESC'); ?>"><?php echo JText::_('WF_LABEL_STYLE'); ?></label>
                <div class="uk-form-controls uk-width-2-10">
                    <select id="border_style">
                        <option value="inherit"><?php echo JText::_('WF_OPTION_NOT_SET'); ?></option>
                        <option value="none"><?php echo JText::_('WF_OPTION_BORDER_NONE'); ?></option>
                        <option value="solid"><?php echo JText::_('WF_OPTION_BORDER_SOLID'); ?></option>
                        <option value="dashed"><?php echo JText::_('WF_OPTION_BORDER_DASHED'); ?></option>
                        <option value="dotted"><?php echo JText::_('WF_OPTION_BORDER_DOTTED'); ?></option>
                        <option value="double"><?php echo JText::_('WF_OPTION_BORDER_DOUBLE'); ?></option>
                        <option value="groove"><?php echo JText::_('WF_OPTION_BORDER_GROOVE'); ?></option>
                        <option value="inset"><?php echo JText::_('WF_OPTION_BORDER_INSET'); ?></option>
                        <option value="outset"><?php echo JText::_('WF_OPTION_BORDER_OUTSET'); ?></option>
                        <option value="ridge"><?php echo JText::_('WF_OPTION_BORDER_RIDGE'); ?></option>
                    </select>
                </div>

                <label for="border_color" class="hastip uk-form-label uk-width-1-10 uk-margin-small-left"
                       title="<?php echo JText::_('WF_LABEL_BORDER_COLOR_DESC'); ?>"><?php echo JText::_('WF_LABEL_COLOR'); ?></label>
                <div class="uk-form-controls uk-width-2-10">
                    <input id="border_color" class="color" type="text" value="#000000" />
                </div>
            </div>
        </div>
    </div>
    <div class="uk-width-1-5">
        <div class="preview">
            <img id="sample" src="<?php echo $this->plugin->image('sample.jpg', 'libraries'); ?>" alt="sample.jpg"/>
            <?php echo JText::_('WF_LOREM_IPSUM'); ?>
        </div>
    </div>
</div>
