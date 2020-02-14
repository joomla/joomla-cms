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

require_once JPATH_ADMINISTRATOR . '/components/com_jce/helpers/browser.php';

class JceModelBrowser extends JModelLegacy
{
    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since   1.6
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app = JFactory::getApplication();
        
        $filter = $app->input->getCmd('filter', '');
        
        $url = WfBrowserHelper::getBrowserLink(null, $filter);

        if (empty($url)) {
            $app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
            $app->redirect('index.php?option=com_jce');
        }

        $this->setState('url', $url);
    }
}