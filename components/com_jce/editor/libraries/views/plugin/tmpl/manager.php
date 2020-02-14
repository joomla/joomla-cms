<?php

/**
 * @copyright 	Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('WF_EDITOR') or die('RESTRICTED');
?>
<div class="uk-position-cover uk-browser uk-browser-<?php echo $this->filebrowser->get('position'); ?>">
<?php
    // render tabs and panels
    WFTabs::getInstance()->render();

    if ($this->filebrowser->get('position') !== 'external') {
        $this->filebrowser->render();
    }
?>
</div>
<div class="actionPanel uk-modal-footer">
	<button class="uk-button uk-button-refresh" id="refresh"><?php echo JText::_('WF_LABEL_REFRESH')?></button>
	<button class="uk-button uk-button-confirm" id="insert"><?php echo JText::_('WF_LABEL_INSERT')?></button>
	<button class="uk-button uk-button-cancel" id="cancel"><?php echo JText::_('WF_LABEL_CANCEL')?></button>
</div>
