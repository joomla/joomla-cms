<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contenthistory\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/**
 * Contenthistory list controller class.
 *
 * @since  3.2
 */
class HistoryController extends AdminController
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The name of the model
	 * @param   string  $prefix  The prefix for the model
	 * @param   array   $config  An additional array of parameters
	 *
	 * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel  The model
	 *
	 * @since   3.2
	 */
	public function getModel($name = 'History', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Toggles the keep forever value for one or more history rows. If it was Yes, changes to No. If No, changes to Yes.
	 *
	 * @return	void
	 *
	 * @since	3.2
	 */
	public function keep()
	{
		$this->checkToken();

		// Get items to toggle keep forever from the request.
		$cid = (array) $this->input->get('cid', array(), 'int');

		// Remove zero values resulting from input filter
		$cid = array_filter($cid);

		if (empty($cid))
		{
			$this->app->enqueueMessage(Text::_('COM_CONTENTHISTORY_NO_ITEM_SELECTED'), 'warning');
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Toggle keep forever status of the selected items.
			if ($model->keep($cid))
			{
				$this->setMessage(Text::plural('COM_CONTENTHISTORY_N_ITEMS_KEEP_TOGGLE', count($cid)));
			}
			else
			{
				$this->setMessage($model->getError(), 'error');
			}
		}

		$this->setRedirect(
			Route::_(
				'index.php?option=com_contenthistory&view=history&layout=modal&tmpl=component&item_id='
				. $this->input->getCmd('item_id') . '&' . Session::getFormToken() . '=1', false
			)
		);
	}

	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   4.0.0
	 */
	protected function getRedirectToListAppend()
	{
		return '&layout=modal&tmpl=component&item_id=' . $this->input->get('item_id') . '&' . Session::getFormToken() . '=1';
	}
}
