<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

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
	 * Overrides JControllerForm::allowEdit
	 *
	 * Checks that non-Super Admins are not editing Super Admins.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   3.8.8
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		// Check for if Super Admin can edit
		$data['id'] = (int) $data['id'];
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__viewlevels'))
			->where($db->quoteName('id') . ' = :id')
			->bind(':id', $data['id'], ParameterType::INTEGER);
		$db->setQuery($query);

		$viewlevel = $db->loadAssoc();

		// Decode level groups
		$groups = json_decode($viewlevel['rules']);

		// If this group is super admin and this user is not super admin, canEdit is false
		if (!$this->app->getIdentity()->authorise('core.admin') && Access::checkGroup($groups[0], 'core.admin'))
		{
			$this->setMessage(Text::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'), 'error');

			$this->setRedirect(
				Route::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list
					. $this->getRedirectToListAppend(), false
				)
			);

			return false;
		}

		return parent::allowEdit($data, $key);
	}

	/**
	 * Removes an item.
	 *
	 * Overrides Joomla\CMS\MVC\Controller\FormController::delete to check the core.admin permission.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function delete()
	{
		// Check for request forgeries.
		$this->checkToken();

		$ids = $this->input->get('cid', array(), 'array');

		if (!$this->app->getIdentity()->authorise('core.admin', $this->option))
		{
			throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
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
			if ($model->delete($ids))
			{
				$this->setMessage(Text::plural('COM_USERS_N_LEVELS_DELETED', count($ids)));
			}
		}

		$this->setRedirect('index.php?option=com_users&view=levels');
	}
}
