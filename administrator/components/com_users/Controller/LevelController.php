<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Users\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\Notallowed;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

/**
 * User view level controller class.
 *
 * @since  1.6
 */
class LevelController extends FormController
{
	/**
	 * @var     string  The prefix to use with controller messages.
	 * @since   1.6
	 */
	protected $text_prefix = 'COM_USERS_LEVEL';

	/**
	 * Method to check if you can save a new or existing record.
	 *
	 * Overrides Joomla\CMS\MVC\Controller\FormController::allowSave to check the core.admin permission.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function allowSave($data, $key = 'id')
	{
		return ($this->app->getIdentity()->authorise('core.admin', $this->option) && parent::allowSave($data, $key));
	}

	/**
	 * Removes an item.
	 *
	 * Overrides Joomla\CMS\MVC\Controller\FormController::delete to check the core.admin permission.
	 *
	 * @return  boolean  Returns true on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function delete()
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$ids = $this->input->get('cid', array(), 'array');

		if (!$this->app->getIdentity()->authorise('core.admin', $this->option))
		{
			throw new Notallowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}
		elseif (empty($ids))
		{
			$this->setMessage(Text::_('COM_USERS_NO_LEVELS_SELECTED'), 'warning');
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			$ids = ArrayHelper::toInteger($ids);

			// Remove the items.
			if (!$model->delete($ids))
			{
				$this->setMessage($model->getError(), 'error');
			}
			else
			{
				$this->setMessage(Text::plural('COM_USERS_N_LEVELS_DELETED', count($ids)));
			}
		}

		$this->setRedirect('index.php?option=com_users&view=levels');
	}
}
