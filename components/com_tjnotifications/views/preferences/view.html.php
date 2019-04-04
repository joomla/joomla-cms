<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Content categories view.
 *
 * @since  1.6
 */
class TJNotificationsViewPreferences extends JViewLegacy
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name  of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */

	public function display($tpl = null)
	{
		// Get data from the model

		$user = JFactory::getUser();

		if ($user->id)
		{
			$this->pagination	= $this->get('Pagination');
			$this->clients		= $this->get('Client');
			$model = $this->getModel();

			for ($i = 0; $i < count($this->clients); $i++)
			{
				$this->keys[$this->clients[$i]->client] = $model->Keys($this->clients[$i]->client);
			}

			$this->preferences = $this->get('States');
			$model = JModelList::getInstance('Providers', 'TJNotificationsModel');

			$this->providers	= $model->getProvider();

			$model = $this->getModel();

			for ($i = 0;$i < count($this->providers); $i++)
			{
				$this->adminPreferences[$this->providers[$i]->provider] = $model->adminPreferences($this->providers[$i]->provider);
			}

			parent::display($tpl);
		}
		else
		{
			$message = JText::sprintf('JGLOBAL_YOU_MUST_LOGIN_FIRST');
			$app = JFactory::getApplication();
			$app->redirect(JRoute::_(JURI::root()), $message);
		}
	}
}
