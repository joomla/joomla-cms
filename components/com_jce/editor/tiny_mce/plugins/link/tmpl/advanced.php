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
        <label class="uk-form-label uk-width-3-10" for="id" class="hastip" title="<?php echo JText::_('WF_LABEL_ID_DESC'); ?>"><?php echo JText::_('WF_LABEL_ID'); ?></label>
        <div class="uk-form-controls uk-width-7-10"><input id="id" type="text" value="" /></div>
    </div>
    <div class="uk-form-row">
        <label class="uk-form-label uk-width-3-10" for="style" class="hastip" title="<?php echo JText::_('WF_LABEL_STYLE_DESC'); ?>"><?php echo JText::_('WF_LABEL_STYLE'); ?></label>
        <div class="uk-form-controls uk-width-7-10"><input type="text" id="style" value="" /></div>
    </div>
    <div class="uk-form-row">
        <label class="uk-form-label uk-width-3-10" for="classes" class="hastip" title="<?php echo JText::_('WF_LABEL_CLASSES_DESC'); ?>"><?php echo JText::_('WF_LABEL_CLASSES'); ?></label>
        <div class="uk-form-controls uk-width-7-10 uk-datalist">
            <input type="text" id="classes" value="" />
            <select id="classlist">
                <option value=""><?php echo JText::_('WF_OPTION_NOT_SET'); ?></option>
            </select>
        </div>
    </div>

    <div class="uk-form-row">
        <label class="uk-form-label uk-width-3-10" for="dir" class="hastip" title="<?php echo JText::_('WF_LABEL_DIR_DESC'); ?>"><?php echo JText::_('WF_LABEL_DIR'); ?></label>
        <div class="uk-form-controls uk-width-7-10">
            <select id="dir">
                <option value=""><?php echo JText::_('WF_OPTION_NOT_SET'); ?></option>
                <option value="ltr"><?php echo JText::_('WF_OPTION_LTR'); ?></option>
                <option value="rtl"><?php echo JText::_('WF_OPTION_RTL'); ?></option>
            </select>
        </div>
    </div>
    <div class="uk-form-row">
        <label class="uk-form-label uk-width-3-10" for="hreflang" class="hastip" title="<?php echo JText::_('WF_LABEL_HREFLANG_DESC'); ?>"><?php echo JText::_('WF_LABEL_HREFLANG'); ?></label>
        <div class="uk-form-controls uk-width-7-10"><input type="text" id="hreflang" value="" /></div>
    </div>
    <div class="uk-form-row">
        <label class="uk-form-label uk-width-3-10" for="lang" class="hastip" title="<?php echo JText::_('WF_LABEL_LANG_DESC'); ?>"><?php echo JText::_('WF_LABEL_LANG'); ?></label>
        <div class="uk-form-controls uk-width-7-10"><input id="lang" type="text" value="" /></div>
    </div>
    <div class="uk-form-row">
        <label class="uk-form-label uk-width-3-10" for="charset" class="hastip" title="<?php echo JText::_('WF_LABEL_CHARSET_DESC'); ?>"><?php echo JText::_('WF_LABEL_CHARSET'); ?></label>
        <div class="uk-form-controls uk-width-7-10"><input type="text" id="charset" value="" /></div>
    </div>
    <div class="uk-form-row">
        <label class="uk-form-label uk-width-3-10" for="type" class="hastip" title="<?php echo JText::_('WF_LABEL_MIME_TYPE_DESC'); ?>"><?php echo JText::_('WF_LABEL_MIME_TYPE'); ?></label>
        <div class="uk-form-controls uk-width-7-10"><input type="text" id="type" value="" /></div>
    </div>
    <div class="uk-form-row">
        <label class="uk-form-label uk-width-3-10" for="rel" class="hastip" title="<?php echo JText::_('WF_LABEL_REL_DESC'); ?>"><?php echo JText::_('WF_LABEL_REL'); ?></label>
        <div class="uk-form-controls uk-width-7-10 uk-datalist">
          <input type="text" id="rel" />
          <select>
                <option value=""><?php echo JText::_('WF_OPTION_NOT_SET'); ?></option>
                <option value="nofollow">No Follow</option>
                <option value="alternate">Alternate</option>
                <option value="designates">Designates</option>
                <option value="stylesheet">Stylesheet</option>
                <option value="start">Start</option>
                <option value="next">Next</option>
                <option value="prev">Prev</option>
                <option value="contents">Contents</option>
                <option value="index">Index</option>
                <option value="glossary">Glossary</option>
                <option value="copyright">Copyright</option>
                <option value="chapter">Chapter</option>
                <option value="subsection">Subsection</option>
                <option value="appendix">Appendix</option>
                <option value="help">Help</option>
                <option value="bookmark">Bookmark</option>
            </select>
        </div>
    </div>
    <div class="uk-form-row">
        <label class="uk-form-label uk-width-3-10" for="rev" class="hastip" title="<?php echo JText::_('WF_LABEL_REV_DESC'); ?>"><?php echo JText::_('WF_LABEL_REV'); ?></label>
        <div class="uk-form-controls uk-width-7-10"><select id="rev">
                <option value=""><?php echo JText::_('WF_OPTION_NOT_SET'); ?></option>
                <option value="alternate">Alternate</option>
                <option value="designates">Designates</option>
                <option value="stylesheet">Stylesheet</option>
                <option value="start">Start</option>
                <option value="next">Next</option>
                <option value="prev">Prev</option>
                <option value="contents">Contents</option>
                <option value="index">Index</option>
                <option value="glossary">Glossary</option>
                <option value="copyright">Copyright</option>
                <option value="chapter">Chapter</option>
                <option value="subsection">Subsection</option>
                <option value="appendix">Appendix</option>
                <option value="help">Help</option>
                <option value="bookmark">Bookmark</option>
            </select>
        </div>
    </div>
    <div class="uk-form-row">
        <label class="uk-form-label uk-width-3-10" for="tabindex" class="hastip" title="<?php echo JText::_('WF_LABEL_TABINDEX_DESC'); ?>"><?php echo JText::_('WF_LABEL_TABINDEX'); ?></label>
        <div class="uk-form-controls uk-width-7-10"><input type="text" id="tabindex" value="" /></div>
    </div>
    <div class="uk-form-row">
        <label class="uk-form-label uk-width-3-10" for="accesskey" class="hastip" title="<?php echo JText::_('WF_LABEL_ACCESSKEY_DESC'); ?>"><?php echo JText::_('WF_LABEL_ACCESSKEY'); ?></label>
        <div class="uk-form-controls uk-width-7-10"><input type="text" id="accesskey" value="" /></div>
    </div>
