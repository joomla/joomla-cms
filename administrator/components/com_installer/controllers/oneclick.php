<?php

/**
 * @author      Jeremy Wilken - Gnome on the run
 * @link        www.gnomeontherun.com
 * @copyright   Copyright 2011 Gnome on the run. All Rights Reserved.
 * @category    Administrator
 * @package     com_installer
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');
// Import dependencies
//require_once JPATH_COMPONENT_ADMINISTRATOR . '/models/extension.php';

/**
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 */
class InstallerControllerOneClick extends JControllerAdmin 
{
    public function install() {
        // Check for request forgeries
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $model = $this->getModel('install');

        $result = $model->install();

        if ($result == true) {
            $cache = JFactory::getCache('mod_menu');
            $cache->clean();
            // TODO: Reset the users acl here as well to kill off any missing bits
        }

        $app = JFactory::getApplication();
        $redirect_url = $app->getUserState('com_installer.redirect_url');
        if (empty($redirect_url)) {
            $redirect_url = JRoute::_('index.php?option=com_installer&view=install', false);
        } else {
            // wipe out the user state when we're going to redirect
            $app->setUserState('com_installer.redirect_url', '');
            $app->setUserState('com_installer.message', '');
            $app->setUserState('com_installer.extension_message', '');
        }
        $this->setRedirect($redirect_url);
    }

    /**
     * Installs an extension from a url
     */
    public function install_remote() {
        JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $model = $this->getModel('oneclick');
        $cid = JRequest::getVar('cid', array(), '', 'array');
		        
		JArrayHelper::toInteger($cid, array());
        if ($model->install_remote($cid)) {
            $cache = JFactory::getCache('mod_menu');
            $cache->clean();
        }

        $app = JFactory::getApplication();
        $redirect_url = $app->getUserState('com_installer.redirect_url');
        if (empty($redirect_url)) {
            $redirect_url = JRoute::_('index.php?option=com_installer&view=install', false);
        } else {
            // wipe out the user state when we're going to redirect
            $app->setUserState('com_installer.redirect_url', '');
            $app->setUserState('com_installer.message', '');
            $app->setUserState('com_installer.extension_message', '');
        }
        $this->setRedirect($redirect_url);
    }

    /**
     * Ajax method for distro management
     * @return  object
     */
    public function distro_download() {
        JRequest::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

        $model = $this->getModel('install');

        echo json_encode($model->distro_download());

        JFactory::getApplication()->close();
    }

    /**
     * Ajax method for distro management
     * @return  object
     */
    public function distro_extract() {
        JRequest::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

        $model = $this->getModel('install');

        echo json_encode($model->distro_extract());

        JFactory::getApplication()->close();
    }

    /**
     * Ajax method for distro management
     * @return  object
     */
    public function distro_install() {
        JRequest::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

        $model = $this->getModel('install');

        echo json_encode($model->distro_install());

        JFactory::getApplication()->close();
    }

    /**
     * Ajax method for distro management
     * @return  object
     */
    public function distro_script_install() {
        JRequest::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

        $model = $this->getModel('install');

        echo json_encode($model->distro_script_install());

        JFactory::getApplication()->close();
    }

    /**
     * Ajax method for distro management
     * @return  object
     */
    public function distro_script_preflight() {
        JRequest::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

        $model = $this->getModel('install');

        echo json_encode($model->distro_script_preflight());

        JFactory::getApplication()->close();
    }

    /**
     * Ajax method for distro management
     * @return  object
     */
    public function distro_script_postflight() {
        JRequest::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

        $model = $this->getModel('install');

        echo json_encode($model->distro_script_postflight());

        JFactory::getApplication()->close();
    }

    /**
     * Ajax method for distro management
     * @return  object
     */
    public function distro_sql() {
        JRequest::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

        $model = $this->getModel('install');

        echo json_encode($model->distro_sql());

        JFactory::getApplication()->close();
    }

    public function distro_cleanup() {
        JRequest::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

        $model = $this->getModel('install');

        echo json_encode($model->distro_cleanup());

        JFactory::getApplication()->close();
    }

    /**
     * Find new updates.
     *
     * @since	2.5
     */
    function find() {
        // Find updates
        // Check for request forgeries
        JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        $model = $this->getModel('install');
        $model->purge();
        $result = $model->findUpdates();

        // Workaround for removing extentions that are already installed without
        // overwriting the Platform. Major breech of MVC but I can live with myself for the time being.
        $db = JFactory::getDBO();
        $updates = $model->getUpdates();
        $extensions = $model->getExtensions();
        $installed = array();
        foreach ($updates as $update) {
            if ($update->extension_id)
                continue;
            foreach ($extensions as $extension) {
                if ($extension->element == $update->element && $extension->folder == $update->folder && $extension->type == $update->type) {
                    $installed[] = $update->update_id;
                    continue;
                }
            }
        }

        if (count($installed)) {

            $db->setQuery('DELETE FROM #__updates WHERE update_id IN (' . implode(',', $installed) . ')');
            if (!$db->query()) {
                $this->setRedirect(JRoute::_('index.php?option=com_installer&view=oneclick'), JText::_('COM_INSTALLER_MSG_UPDATEERROR'));
            }
        }
        // End Workaround

        $this->setRedirect(JRoute::_('index.php?option=com_installer&view=oneclick', false));
    }

    /**
     * Purges updates.
     *
     * @since	2.5
     */
    function purge() {
        // Purge updates
        // Check for request forgeries
        JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        $model = $this->getModel('install');
        $model->purge();
        $this->setRedirect(JRoute::_('index.php?option=com_installer&view=oneclick', false), $model->_message);
    }
}
?>
