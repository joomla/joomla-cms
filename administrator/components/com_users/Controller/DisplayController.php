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
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/**
 * Users master display controller.
 *
 * @since  1.6
 */
class DisplayController extends BaseController
{
	/**
	 * The default view.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $default_view = 'users';

	/**
	 * Checks whether a user can see this view.
	 *
	 * @param   string  $view  The view name.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function canView($view)
	{
		$canDo = ContentHelper::getActions('com_users');

		switch ($view)
		{
			// Special permissions.
			case 'groups':
			case 'group':
			case 'levels':
			case 'level':
				return $canDo->get('core.admin');
				break;

			// Default permissions.
			default:
				return true;
		}
	}

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types,
	 *                               for valid values see {@link Joomla\CMS\Filter\InputFilter::clean()}.
	 *
	 * @return  BaseController	 This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = array())
	{
		$view   = $this->input->get('view', 'users');
		$layout = $this->input->get('layout', 'default');
		$id     = $this->input->getInt('id');

		if (!$this->canView($view))
		{
			throw new Notallowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		// Check for edit form.
		if ($view == 'user' && $layout == 'edit' && !$this->checkEditId('com_users.edit.user', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
			$this->setRedirect(Route::_('index.php?option=com_users&view=users', false));

			return false;
		}
		elseif ($view == 'group' && $layout == 'edit' && !$this->checkEditId('com_users.edit.group', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
			$this->setRedirect(Route::_('index.php?option=com_users&view=groups', false));

			return false;
		}
		elseif ($view == 'level' && $layout == 'edit' && !$this->checkEditId('com_users.edit.level', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
			$this->setRedirect(Route::_('index.php?option=com_users&view=levels', false));

			return false;
		}
		elseif ($view == 'note' && $layout == 'edit' && !$this->checkEditId('com_users.edit.note', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
			$this->setRedirect(Route::_('index.php?option=com_users&view=notes', false));

			return false;
		}

		return parent::display($cachable, $urlparams);
	}
}
