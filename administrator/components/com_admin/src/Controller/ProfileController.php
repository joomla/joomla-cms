<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Admin\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/**
 * User profile controller class.
 *
 * @since  1.6
 */
class ProfileController extends FormController
{
	/**
	 * Method to check if you can edit a record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function allowEdit($data = [], $key = 'id')
	{
		return isset($data['id']) && $data['id'] == $this->app->getIdentity()->id;
	}

	/**
	 * Overrides parent save method to check the submitted passwords match.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since   3.2
	 */
	public function save($key = null, $urlVar = null)
	{
		$result = parent::save();

		if ($this->getTask() !== 'apply')
		{
			$return = base64_decode($this->input->get('return', '', 'BASE64'));

			if ($return !== '' && Uri::isInternal($return))
			{
				// Redirect to return URL.
				$this->setRedirect(Route::_($return, false));
			}
			else
			{
				// Redirect to the main page.
				$this->setRedirect(Route::_('index.php', false));
			}
		}

		return $result;
	}

	/**
	 * Method to cancel an edit.
	 *
	 * @param   string  $key  The name of the primary key of the URL variable.
	 *
	 * @return  boolean  True if access level checks pass, false otherwise.
	 *
	 * @since   1.6
	 */
	public function cancel($key = null)
	{
		$result = parent::cancel($key);
		$return = base64_decode($this->input->get('return', '', 'BASE64'));

		if ($return !== '' && Uri::isInternal($return))
		{
			// Redirect to return URL.
			$this->setRedirect(Route::_($return, false));
		}
		else
		{
			// Redirect to the main page.
			$this->setRedirect(Route::_('index.php', false));
		}

		return $result;
	}
}
