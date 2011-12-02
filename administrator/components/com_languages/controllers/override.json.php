<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-2.0/JG/trunk/administrator/components/com_joomgallery/controllers/votes.php $
// $Id: votes.php 3378 2011-10-07 18:37:56Z aha $
/****************************************************************************************\
**   JoomGallery 2                                                                      **
**   By: JoomGallery::ProjectTeam                                                       **
**   Copyright (C) 2008 - 2011  JoomGallery::ProjectTeam                                **
**   Based on: JoomGallery 1.0.0 by JoomGallery::ProjectTeam                            **
**   Released under GNU GPL Public License                                              **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look                       **
**   at administrator/components/com_joomgallery/LICENSE.TXT                            **
\****************************************************************************************/

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

jimport('joomla.application.component.controllerform');

/**
 * JoomGallery Votes Controller
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class LanguagesControllerOverride extends JControllerForm
{
	/**
	 * Method to save a record.
	 *
	 * @param   string  $key	The name of the primary key of the URL variable.
	 * @param   string  $urlVar	The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 * @since   11.1
	 */
	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		//JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

    require_once JPATH_COMPONENT.DS.'helpers'.DS.'jsonresponse.php';

		// Initialise variables.
		$app		= JFactory::getApplication();
		$lang		= JFactory::getLanguage();
		$model		= $this->getModel();
		$data		= JRequest::getVar('jform', array(), 'post', 'array');
		$context	= "$this->option.edit.$this->context";
		$task		= $this->getTask();

		// Determine the name of the primary key for the data.
		if (empty($key)) {
			$key = 'id';
		}

		// To avoid data collisions the urlVar may be different from the primary key.
		if (empty($urlVar)) {
			$urlVar = $key;
		}

		$recordId	= JRequest::getCmd($urlVar);

		$data[$key] = $recordId;

		$session	= JFactory::getSession();
		$registry	= $session->get('registry');

		// Access check.
		if (!$this->allowSave($data, $key)) {
      echo new JoomJsonResponse(new Exception(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED')));

			return;
		}

		// Validate the posted data.
		// Sometimes the form needs some posted data, such as for plugins and modules.
		$form = $model->getForm($data, false);

		if (!$form) {
			echo new JoomJsonResponse(new Exception($model->getError()));

			return;
		}

		// Test whether the data is valid.
		$validData = $model->validate($form, $data);

		// Check for validation errors.
		if ($validData === false) {
			// Get the validation messages.
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if (JError::isError($errors[$i])) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

      echo new JoomJsonResponse(new Exception('Invalid form'));

			return;
		}

		// Attempt to save the data.
		if (!$model->save($validData)) {
      echo new JoomJsonResponse(new Exception(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError())));

			return;
		}

		$this->setMessage(JText::_('COM_OVERRIDER_SAVE_SUCCESS'));

    echo new JoomJsonResponse();

    return;
	}
}