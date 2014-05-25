<?php
/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

include_once dirname(__FILE__).'/../default/view.php';

class InstallerViewOneclick extends InstallerViewDefault
{
    public function display($tpl = null)
    {
        $paths = new stdClass();
        $paths->first = '';

        $this->assignRef('paths', $paths);
        $this->state = $this->get('State');
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->form = $this->get('Form');

        if ($this->getLayout() == 'distribution') {
            jimport('joomla.installer.installer');

            // Extract the list and return it
            $installer = JInstaller::getInstance();
            $installer->setPath('source', $this->state->get('install.directory'));
            $manifest = $installer->getManifest();

            $result = false;
            
            if ($manifest) {
                $result->extensions = isset($manifest->extensions) ? $manifest->extensions : false;
                $result->sql = isset($manifest->install->sql->file) ? $installer->getPath('source') . '/' . $manifest->install->sql->file : false;
                $result->script = isset($manifest->scriptfile) ? $installer->getPath('source') . '/' . $manifest->scriptfile : false;
                if ($result->script) {
                    include($installer->getPath('source') . '/' . $manifest->scriptfile);
                    $class = JRequest::$manifest->name . 'InstallerScript';
                    $script = new $class();
                    $result->scriptclass = $class;
                    $result->preflight = method_exists($script, 'preflight') ? $installer->getPath('source') . '/' . $manifest->scriptfile : false;
                    $result->postflight = method_exists($script, 'postflight') ? $installer->getPath('source') . '/' . $manifest->scriptfile : false;
                }
            }

            $this->source = base64_encode($installer->getPath('source'));
            $this->result = $result;
            
        }
        
        parent::display($tpl);
    }
    
    protected function addToolbar()
    {        
        JToolBarHelper::custom('oneclick.install_remote', 'upload', 'upload', 'JTOOLBAR_INSTALL', true, false);
        JToolBarHelper::custom('oneclick.find', 'refresh', 'refresh', 'COM_INSTALLER_TOOLBAR_FIND_EXTENSIONS', false, false);
        JToolBarHelper::custom('oneclick.purge', 'purge', 'purge', 'JTOOLBAR_PURGE_CACHE', false, false);
        JToolBarHelper::divider();
        parent::addToolbar();
    }
}