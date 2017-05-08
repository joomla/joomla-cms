<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Save Controller for module editing
 *
 * @package     Joomla.Site
 * @subpackage  com_config
 * @since       3.2
*/
class ConfigControllerModulesSave extends JControllerBase
{
	/**
	 * Method to save module editing.
	 *
	 * @return  bool	True on success.
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		// Check for request forgeries.
		if (!JSession::checkToken())
		{
			$this->getApplication()->enqueueMessage(JText::_('JINVALID_TOKEN'));
			$this->getApplication()->redirect('index.php');
		}

		// Check if the user is authorized to do this.
		$user = JFactory::getUser();

		if (!$user->authorise('module.edit.frontend', 'com_modules.module.' . $this->getInput()->get('id'))
			&& !$user->authorise('module.edit.frontend', 'com_modules'))
		{
			$this->getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'));
			$this->getApplication()->redirect('index.php');
		}

		// Set FTP credentials, if given.
		JClientHelper::setCredentialsFromRequest('ftp');

		// Get sumitted module id
		$moduleId = '&id=' . $this->getInput()->get('id');

		// Get returnUri
		$returnUri = $this->getInput()->post->get('return', null, 'base64');
		$redirect = '';

		if (!empty($returnUri))
		{
			$redirect = '&return=' . $returnUri;
		}

		JLoader::register('ModulesDispatcher', JPATH_ADMINISTRATOR . '/components/com_modules/dispatcher.php');

		$app = \Joomla\CMS\Application\CmsApplication::getInstance('administrator');
		$app->loadLanguage($this->getApplication()->getLanguage());
		$dispatcher      = new ModulesDispatcher($app, $this->getInput());

		$controllerClass = $dispatcher->getController('Module');

		// Get a document object
		$document = JFactory::getDocument();

		// Set backend required params
		$document->setType('json');

		// Execute backend controller
		$return = $controllerClass->save();

		// Reset params back after requesting from service
		$document->setType('html');

		// Check the return value.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_config.modules.global.data', $data);

			// Save failed, go back to the screen and display a notice.
			$this->getApplication()->enqueueMessage(JText::_('JERROR_SAVE_FAILED'));
			$this->getApplication()->redirect(JRoute::_('index.php?option=com_config&controller=config.display.modules' . $moduleId . $redirect, false));
		}

		// Redirect back to com_config display
		$this->getApplication()->enqueueMessage(JText::_('COM_CONFIG_MODULES_SAVE_SUCCESS'));

		// Set the redirect based on the task.
		switch ($this->options[3])
		{
			case 'apply':
				$this->getApplication()->redirect(JRoute::_('index.php?option=com_config&controller=config.display.modules' . $moduleId . $redirect, false));
				break;

			case 'save':
			default:

				if (!empty($returnUri))
				{
					$redirect = base64_decode(urldecode($returnUri));

					// Don't redirect to an external URL.
					if (!JUri::isInternal($redirect))
					{
						$redirect = JUri::base();
					}
				}
				else
				{
					$redirect = JUri::base();
				}
				$this->getApplication()->redirect($redirect);
				break;
		}
	}
}
