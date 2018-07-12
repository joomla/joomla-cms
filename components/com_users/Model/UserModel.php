<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Users\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\User\User;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Language\Multilanguage;
use Joomla\Registry\Registry;

/**
 * Public Profile model class for Users.
 *
 * @since  4.0
 */
class UserModel extends FormModel
{
	/**
	 * A loaded item
	 *
	 * @since   1.6
	 */
	protected $_item = null;

	protected $user;

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState()
	{
		$app = Factory::getApplication();

		// Load state from the request.
		$pk = $app->input->getInt('id');
		$this->setState('user.id', $pk);

		$offset = $app->input->getUInt('limitstart');
		$this->setState('list.offset', $offset);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

	}

	/**
	 * Method to get user data.
	 *
	 * @param   integer  $pk  The id of the user.
	 *
	 * @return  object  User instance
	 *
	 * @throws \Exception
	 */
	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('user.id');

		if ($this->_item === null)
		{
			$this->_item = array();
		}

		if (!isset($this->_item[$pk]))
		{
			$user = User::getInstance($pk);

			if (empty($user))
			{
				throw new \Exception(Text::_('COM_USERS_ERROR_USER_NOT_FOUND'), 404);
			}

			$loggedUser = Factory::getUser();
			$groups = $loggedUser->getAuthorisedViewLevels();

			$registry = new Registry($user->params);
			$user->params = $this->getState('params');
			$user->params = clone $this->getState('params');
			$user->params->merge($registry);


			// Compute view access permissions.
			$user->params->set('access-view', in_array($user->access, $groups));

			$this->_item[$pk] = $user;
		}

		return $this->_item[$pk];
	}

	/**
	 * Method to get the contact form.
	 * The base form is loaded from XML and then an event is fired
	 *
	 * @param   array    $data      An optional array of data for the form to interrogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  \JForm  A \JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_users.contact', 'contact', array('control' => 'jform', 'load_data' => true));

		if (empty($form))
		{
			return false;
		}

		$user = $this->_item[$this->getState('user.id')];

		if (!$user->params->get('show_email_copy', 0))
		{
			$form->removeField('contact_email_copy');
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  array    The default data is an empty array.
	 *
	 * @since   1.6.2
	 * @throws  \Exception
	 */
	protected function loadFormData()
	{
		$data = (array) Factory::getApplication()->getUserState('com_users.contact.data', array());

		if (empty($data['language']) && Multilanguage::isEnabled())
		{
			$data['language'] = Factory::getLanguage()->getTag();
		}

		$this->preprocessData('com_users.contact', $data);

		return $data;
	}
}
