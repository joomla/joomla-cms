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
<div class="uk-form-row">
    <label for="style" class="hastip uk-form-label uk-width-3-10"
           title="<?php echo JText::_('WF_LABEL_STYLE_DESC'); ?>"><?php echo JText::_('WF_LABEL_STYLE'); ?></label>
    <div class="uk-form-controls uk-width-7-10"><input id="style" type="text" value=""/></div>
</div>
<div class="uk-form-row">
    <label for="classlist" class="hastip uk-form-label uk-width-3-10"
           title="<?php echo JText::_('WF_LABEL_CLASSES_DESC'); ?>"><?php echo JText::_('WF_LABEL_CLASSES'); ?></label>
    <div class="uk-form-controls uk-width-7-10 uk-datalist">
        <input id="classes" type="text" value=""/>
        <select id="classlist">
          <option value=""><?php echo JText::_('WF_OPTION_NOT_SET'); ?></option>
        </select>
    </div>
</div>
<div class="uk-form-row">
    <label for="title" class="hastip uk-form-label uk-width-3-10"
           title="<?php echo JText::_('WF_LABEL_TITLE_DESC'); ?>"><?php echo JText::_('WF_LABEL_TITLE'); ?></label>
    <div class="uk-form-controls uk-width-7-10"><input id="title" type="text" value=""/></div>
</div>
<div class="uk-form-row">
    <label for="id" class="hastip uk-form-label uk-width-3-10"
           title="<?php echo JText::_('WF_LABEL_ID_DESC'); ?>"><?php echo JText::_('WF_LABEL_ID'); ?></label>
    <div class="uk-form-controls uk-width-7-10"><input id="id" type="text" value=""/></div>
</div>

<div class="uk-form-row">
    <label for="dir" class="hastip uk-form-label uk-width-3-10"
           title="<?php echo JText::_('WF_LABEL_DIR_DESC'); ?>"><?php echo JText::_('WF_LABEL_DIR'); ?></label>
    <div class="uk-form-controls uk-width-7-10">
        <select id="dir">
            <option value=""><?php echo JText::_('WF_OPTION_NOT_SET'); ?></option>
            <option value="ltr"><?php echo JText::_('WF_OPTION_LTR'); ?></option>
            <option value="rtl"><?php echo JText::_('WF_OPTION_RTL'); ?></option>
        </select>
    </div>
</div>

<div class="uk-form-row">
    <label for="lang" class="hastip uk-form-label uk-width-3-10"
           title="<?php echo JText::_('WF_LABEL_LANG_DESC'); ?>"><?php echo JText::_('WF_LABEL_LANG'); ?></label>
    <div class="uk-form-controls uk-width-7-10"><input id="lang" type="text" value=""/></div>
</div>

<div class="uk-form-row">
    <label for="usemap" class="hastip uk-form-label uk-width-3-10"
           title="<?php echo JText::_('WF_LABEL_USEMAP_DESC'); ?>"><?php echo JText::_('WF_LABEL_USEMAP'); ?></label>
    <div class="uk-form-controls uk-width-7-10"><input id="usemap" type="text" value=""/></div>
</div>

<div class="uk-form-row html4">
    <label for="longdesc" class="hastip uk-form-label uk-width-3-10"
           title="<?php echo JText::_('WF_LABEL_LONGDESC_DESC'); ?>"><?php echo JText::_('WF_LABEL_LONGDESC'); ?></label>
    <div class="uk-form-controls uk-width-7-10"><input id="longdesc" type="text" value="" class="browser html" /></div>
</div>
