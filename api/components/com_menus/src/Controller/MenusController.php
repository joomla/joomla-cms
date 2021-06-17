<?php
/**
 * @package     Joomla.API
 * @subpackage  com_menus
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Api\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\ApiController;

/**
 * The menus controller
 *
 * @since  4.0.0
 */
class MenusController extends ApiController
{
	/**
	 * The content type of the item.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $contentType = 'menus';

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $default_view = 'menus';

	/**
	 * Basic display of an item view
	 *
	 * @param   integer  $id  The primary key to display. Leave empty if you want to retrieve data from the request
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @since   4.0.0
	 */
	public function displayItem($id = null)
	{
		$this->modelState->set('filter.client_id', $this->getClientIdFromInput());

		return parent::displayItem($id);
	}

	/**
	 * Basic display of a list view
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @since   4.0.0
	 */
	public function displayList()
	{
		$this->modelState->set('filter.client_id', $this->getClientIdFromInput());

		return parent::displayList();
	}

	/**
	 * Get client id from input
	 *
	 * @return string
	 *
	 * @since 4.0.0
	 */
	private function getClientIdFromInput()
	{
		return $this->input->exists('client_id') ?
			$this->input->get('client_id') : $this->input->post->get('client_id');
	}
}
