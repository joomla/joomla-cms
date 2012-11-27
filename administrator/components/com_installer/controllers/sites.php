<?php

/**
 * @author      Jeremy Wilken - Gnome on the run
 * @link        www.gnomeontherun.com
 * @copyright   Copyright 2011 Gnome on the run. All Rights Reserved.
 * @category    
 * @package     
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

class InstallerControllerSites extends JControllerAdmin 
{
    	
	public function publish()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$user	= JFactory::getUser();
		$ids	= JRequest::getVar('cid', array(), '', 'array');
		$values	= array('publish' => 1, 'unpublish' => 0);
		$task	= $this->getTask();
		$value	= JArrayHelper::getValue($values, $task, 0, 'int');

		if (empty($ids)) {
			JError::raiseWarning(500, JText::_('COM_INSTALLER_ERROR_NO_SITES_SELECTED'));
		} else {
			// Get the model.
			$model	= $this->getModel('sites');

			// Change the state of the records.
			if (!$model->publish($ids, $value)) {
				JError::raiseWarning(500, implode('<br />', $model->getErrors()));
			} else {
				if ($value == 1) {
					$ntext = 'COM_INSTALLER_N_SITES_PUBLISHED';
				} elseif ($value == 0) {
					$ntext = 'COM_INSTALLER_N_SITES_UNPUBLISHED';
				}
				$this->setMessage(JText::plural($ntext, count($ids)));
			}
		}

		$this->setRedirect(JRoute::_('index.php?option=com_installer&view=sites', false));
	}
    
}