<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_trash
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Trash\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;

/**
 * Trash Controller
 *
 * @since  __DEPLOY_VERSION__
 */
class DisplayController extends BaseController
{
	/**
	 * The default view.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $default_view = 'trash';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 */
	public function display($cachable = false, $urlparams = array())
	{
		return parent::display();
	}

	/**
	 * Trash a list of items.
	 *
	 * @return  void
	 */
	public function trash()
	{
		// Check for request forgeries
		$this->checkToken();

		$ids = $this->input->get('cid', array(), 'array');

		if (empty($ids))
		{
			$this->app->enqueueMessage(Text::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'), 'warning');
		}
		else
		{
			// Get the model.
			/** @var \Joomla\Component\Trash\Administrator\Model\TrashModel $model */
			$model = $this->getModel('Trash');

			// Trash the items.
			$this->setMessage(Text::plural('COM_TRASH_N_ITEMS_DELETED', $model->trash($ids)));
		}

		$this->setRedirect('index.php?option=com_trash');
	}

	/**
	 * Provide the data for a badge in a menu item via JSON
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getMenuBadgeData()
	{
		if (!Factory::getUser()->authorise('core.admin', 'com_trash'))
		{
			throw new \Exception(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'));
		}

		$model = $this->getModel('Trash');

		$amount = (int) count($model->getItems());

		echo new JsonResponse($amount);
	}

	/**
	 * Method to get the number of trashed items
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getQuickiconContent()
	{
		if (!Factory::getUser()->authorise('core.admin', 'com_trash'))
		{
			throw new \Exception(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'));
		}

		$model = $this->getModel('Trash');

		$amount = (int) count($model->getItems());

		$result = [];

		$result['amount'] = $amount;
		$result['sronly'] = Text::plural('COM_TRASH_N_QUICKICON_SRONLY', $amount);

		echo new JsonResponse($result);
	}
}
