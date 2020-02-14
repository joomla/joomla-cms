<?php

/**
 * @copyright 	Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('_WF_EXT') or die('RESTRICTED');

class WFPopupsExtension_Jcemediabox
{
    /**
     * Constructor activating the default information of the class.
     */
    public function __construct()
    {
        if (self::isEnabled()) {
            $scripts = array();

            $document = WFDocument::getInstance();

            $document->addScript('jcemediabox', 'extensions/popups/jcemediabox/js');
            $document->addStyleSheet('jcemediabox', 'extensions/popups/jcemediabox/css');

            jimport('joomla.filesystem.folder');
            jimport('joomla.filesystem.file');

            $path = JPATH_PLUGINS.'/system/jcemediabox/addons';

            $files = JFolder::files($path, '.js');

            if (!empty($files)) {
                foreach ($files as $file) {
                    if (strpos($file, '-src.js') === false) {
                        $scripts[] = 'plugins/system/jcemediabox/addons/'.JFile::stripExt($file);
                    }
                }
            }

            $document->addScript($scripts, 'joomla');
        }
    }

    public function getParams()
    {
        $wf = WFEditorPlugin::getInstance();

        return array(
            'width' => 600,
            'album' => '#jcemediabox_popup_group',
            'multiple' => '#jcemediabox_popup_title,#jcemediabox_popup_caption',
            'attribute' => $wf->getParam('popups.jcemediabox.attribute', 'data-mediabox'),
            'popup_group' => $wf->getParam('popups.jcemediabox.popup_group', ''),
            'popup_icon' => $wf->getParam('popups.jcemediabox.popup_icon', 1),
            'popup_icon_position' => $wf->getParam('popups.jcemediabox.popup_icon_position', ''),
            'popup_autopopup' => $wf->getParam('popups.jcemediabox.popup_autopopup', ''),
            'popup_hide' => $wf->getParam('popups.jcemediabox.popup_hide', 0),
            'popup_mediatype' => $wf->getParam('popups.jcemediabox.popup_mediatype', ''),
        );
    }

    public function isEnabled()
    {
        $wf = WFEditorPlugin::getInstance();

        if ((JPluginHelper::isEnabled('system', 'jcemediabox') || JPluginHelper::isEnabled('system', 'wf_lightcase')) && $wf->getParam('popups.jcemediabox.enable', 1) == 1) {
            return true;
        }

        return false;
    }

    public function checkVersion()
    {
        return true;
    }
}
