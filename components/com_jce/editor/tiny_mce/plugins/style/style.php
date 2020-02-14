<?php

/**
 * @copyright     Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('JPATH_PLATFORM') or die;

require_once WF_EDITOR_LIBRARIES . '/classes/plugin.php';

class WFStylePlugin extends WFEditorPlugin
{
    public function __construct()
    {
        parent::__construct(array('colorpicker' => true));
    }

    /**
     * Display the plugin.
     */
    public function display()
    {
        parent::display();

        $document = WFDocument::getInstance();

        $document->addScript(array('style'), 'plugins');
        $document->addStyleSheet(array('style'), 'plugins');

        $settings = $this->getSettings();

        $document->addScriptDeclaration('StyleDialog.settings=' . json_encode($settings) . ';');

        $tabs = WFTabs::getInstance(array(
            'base_path' => WF_EDITOR_PLUGIN,
        ));

        // Add tabs
        $tabs->addTab('text');
        $tabs->addTab('background');
        $tabs->addTab('block');
        $tabs->addTab('box');
        $tabs->addTab('border');
        $tabs->addTab('list');
        $tabs->addTab('positioning');
    }

    public function getSettings($settings = array())
    {
        $profile = $this->getProfile();

        $settings = array(
            'file_browser' => $this->getParam('file_browser', 1) && in_array('browser', explode(',', $profile->plugins)),
        );

        return parent::getSettings($settings);
    }
}
