<?php
/**
 * @package     Joomla.API
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Modules\Api\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\ApiController;

/**
 * The modules controller
 *
 * @since  4.0.0
 */
class ModulesController extends ApiController
{
	/**
	 * The content type of the item.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $contentType = 'modules';

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $default_view = 'modules';

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
		if ($id === null)
		{
			$id = $this->input->get('id', '', 'string');
		}

		$clientId = $this->input->exists('client_id') ?
			$this->input->get('client_id') : $this->input->post->get('client_id');

		$this->input->set('model_state', ['client_id' => $clientId]);

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
		$clientId = $this->input->exists('client_id') ?
			$this->input->get('client_id') : $this->input->post->get('client_id');

		$this->input->set('model_state', ['client_id' => $clientId]);

		return parent::displayList();
	}
}
