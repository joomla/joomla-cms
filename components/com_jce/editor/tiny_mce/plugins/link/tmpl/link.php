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

$search = $this->plugin->getSearch('link');
$links = $this->plugin->getLinks();

?>
<div class="uk-form-row">
    <label class="uk-form-label uk-width-1-5" for="href" class="hastip" title="<?php echo JText::_('WF_LABEL_URL_DESC'); ?>"><?php echo JText::_('WF_LABEL_URL'); ?></label>
    <div class="uk-form-controls uk-form-icon uk-form-icon-flip uk-width-4-5">
        <input id="href" type="text" value="" required class="browser" />
        <button class="email uk-icon uk-icon-email uk-button uk-button-link" aria-haspopup="true" aria-label="<?php echo JText::_('WF_LABEL_EMAIL'); ?>" title="<?php echo JText::_('WF_LABEL_EMAIL'); ?>"></button>
    </div>
</div>
<div class="uk-form-row">
    <label for="text" class="uk-form-label uk-width-1-5 hastip" title="<?php echo JText::_('WF_LINK_LINK_TEXT_DESC'); ?>"><?php echo JText::_('WF_LINK_LINK_TEXT'); ?></label>
    <div class="uk-form-controls uk-width-4-5">
        <input id="text" type="text" value="" required placeholder="<?php echo JText::_('WF_ELEMENT_SELECTION'); ?>" />
    </div>
</div>
<?php if ($search->isEnabled() || count($links->getLists())) : ?>
    <div id="link-options" class="uk-placeholder">
        <?php echo $search->render(); ?>
        <?php echo $links->render(); ?>
    </div>
<?php endif; ?>
<div class="uk-form-row" id="attributes-anchor">
    <label for="anchor" class="uk-form-label uk-width-1-5 hastip" title="<?php echo JText::_('WF_LABEL_ANCHORS_DESC'); ?>"><?php echo JText::_('WF_LABEL_ANCHORS'); ?></label>
    <div class="uk-form-controls uk-width-4-5" id="anchor_container"></div>
</div>

<div class="uk-form-row" id="attributes-target">
    <label for="target" class="uk-form-label uk-width-1-5 hastip" title="<?php echo JText::_('WF_LABEL_TARGET_DESC'); ?>"><?php echo JText::_('WF_LABEL_TARGET'); ?></label>
    <div class="uk-form-controls uk-width-4-5">
        <select id="target">
            <option value=""><?php echo JText::_('WF_OPTION_NOT_SET'); ?></option>
            <option value="_self"><?php echo JText::_('WF_OPTION_TARGET_SELF'); ?></option>
            <option value="_blank"><?php echo JText::_('WF_OPTION_TARGET_BLANK'); ?></option>
            <option value="_parent"><?php echo JText::_('WF_OPTION_TARGET_PARENT'); ?></option>
            <option value="_top"><?php echo JText::_('WF_OPTION_TARGET_TOP'); ?></option>
        </select>
    </div>
</div>

<div class="uk-form-row" id="attributes-title">
    <label class="uk-form-label uk-width-1-5" for="title" class="hastip" title="<?php echo JText::_('WF_LABEL_TITLE_DESC'); ?>"><?php echo JText::_('WF_LABEL_TITLE'); ?></label>
    <div class="uk-form-controls uk-width-4-5">
        <input id="title" type="text" value="" />
    </div>
</div>
