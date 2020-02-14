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

class WFTablePlugin extends WFEditorPlugin
{
    public function __construct()
    {
        parent::__construct(array('colorpicker' => true));
    }

    public function getLayout()
    {
        return JFactory::getApplication()->input->getWord('layout', 'table');
    }

    /**
     * Display the plugin.
     */
    public function display()
    {
        parent::display();

        $layout = $this->getLayout();
        $document = WFDocument::getInstance();

        $document->addScript(array('table'), 'plugins');
        $document->addStyleSheet(array('table'), 'plugins');

        // update title
        if ($layout !== 'table') {
            $document->setTitle(JText::_('WF_TABLE_' . strtoupper($layout) . '_TITLE'));
        }

        $settings = $this->getSettings();

        $document->addScriptDeclaration('TableDialog.settings=' . json_encode($settings) . ';');

        $tabs = WFTabs::getInstance(array('base_path' => WF_EDITOR_PLUGIN));

        if ($layout == 'merge') {
            // Add tabs
            $tabs->addTab('merge');
        } else {
            $tabs->addTab('general', 1, array('plugin' => $this));
            $tabs->addTab('advanced', 1, array('plugin' => $this));
        }
    }

    public function getSettings($settings = array())
    {
        $profile = $this->getProfile();

        $settings['file_browser'] = $this->getParam('file_browser', 1) && in_array('browser', explode(',', $profile->plugins));

        return parent::getSettings($settings);
    }
}
