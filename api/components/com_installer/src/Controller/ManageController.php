<?php
/**
 * @package     Joomla.API
 * @subpackage  com_installer
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Api\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\ApiController;

/**
 * The manage controller
 *
 * @since  4.0.0
 */
class ManageController extends ApiController
{
	/**
	 * The content type of the item.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $contentType = 'manage';

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $default_view = 'manage';

	/**
	 * Extension list view amended to add filtering of data
	 *
	 * @return  static  A BaseController object to support chaining.
	 *
	 * @since   4.0.0
	 */
	public function displayList()
	{
		$requestBool = $this->input->get('core', $this->input->get->get('core'));
		if (($requestBool === 'true') || ($requestBool === 'false'))
		{
			$this->modelState->set('filter.core', ($requestBool === 'true') ? '1' : '0', 'STRING');
		}

		$this->modelState->set('filter.status', $this->input->get('status', $this->input->get->get('status')), 'INT');
		$this->modelState->set('filter.type', $this->input->get('type', $this->input->get->get('type')), 'STRING');

		return parent::displayList();
	}
}
