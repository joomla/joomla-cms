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

class JceControllerProfiles extends JControllerAdmin
{
    /**
     * Method to import profile data from an XML file.
     *
     * @since   3.0
     */
    public function import()
    {
        // Check for request forgeries
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();

        $model = $this->getModel();

        $result = $model->import();

        // Get redirect URL
        $redirect_url = JRoute::_('index.php?option=com_jce&view=profiles', false);

        // Push message queue to session because we will redirect page by Javascript, not $app->redirect().
        // The "application.queue" is only set in redirect() method, so we must manually store it.
        $app->getSession()->set('application.queue', $app->getMessageQueue());

        header('Content-Type: application/json');

        echo new JResponseJson(array('redirect' => $redirect_url), "", !$result);

        exit();
    }

    public function repair()
    {
        // Check for request forgeries
        JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

        $model = $this->getModel('profiles');

        try {
            $model->repair();
        } catch (Exception $e) {
            $this->setMessage($e->getMessage(), 'error');
        }

        $this->setRedirect('index.php?option=com_jce&view=profiles');
    }

    public function copy()
    {
        // Check for request forgeries
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $user = JFactory::getUser();
        $cid = $this->input->get('cid', array(), 'array');

        // Access checks.
        if (!$user->authorise('core.create', 'com_jce')) {
            throw new Exception(JText::_('JLIB_APPLICATION_ERROR_CREATE_NOT_PERMITTED'));
        }

        if (empty($cid)) {
            throw new Exception(JText::_('No Item Selected'));
        } else {
            $model = $this->getModel();
            // Copy the items.
            try {
                $model->copy($cid);
                $ntext = $this->text_prefix . '_N_ITEMS_COPIED';
                $this->setMessage(JText::plural($ntext, count($cid)));
            } catch (Exception $e) {
                $this->setMessage($e->getMessage(), 'error');
            }
        }

        $this->setRedirect('index.php?option=com_jce&view=profiles');
    }

    public function export()
    {
        // Check for request forgeries
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $user = JFactory::getUser();
        $ids = $this->input->get('cid', array(), 'array');

        // Access checks.
        if (!$user->authorise('core.create', 'com_jce')) {
            throw new Exception(JText::_('JLIB_APPLICATION_ERROR_CREATE_NOT_PERMITTED'));
        }

        if (empty($ids)) {
            throw new Exception(JText::_('No Item Selected'));
        } else {
            $model = $this->getModel();
            // Publish the items.
            if (!$model->export($ids)) {
                throw new Exception($model->getError());
            }
        }
    }

    /**
     * Proxy for getModel.
     *
     * @param string $name   The model name. Optional
     * @param string $prefix The class prefix. Optional
     * @param array  $config The array of possible config values. Optional
     *
     * @return object The model
     *
     * @since   1.6
     */
    public function getModel($name = 'Profile', $prefix = 'JceModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }
}
