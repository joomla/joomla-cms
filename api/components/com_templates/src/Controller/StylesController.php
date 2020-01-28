<?php
/**
 * @package     Joomla.API
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Templates\Api\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\ApiController;

/**
 * The styles controller
 *
 * @since  4.0.0
 */
class StylesController extends ApiController
{
	/**
	 * The content type of the item.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $contentType = 'styles';

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $default_view = 'styles';

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
		$this->input->set('model_state', ['client_id' => $this->getClientIdFromInput()]);

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
		$this->input->set('model_state', ['client_id' => $this->getClientIdFromInput()]);

		return parent::displayList();
	}

	/**
	 * Method to save a record.
	 *
	 * @param   integer  $recordKey  The primary key of the item (if exists)
	 *
	 * @return  integer  The record ID on success, false on failure
	 *
	 * @since   4.0.0
	 */
	protected function save($recordKey = null)
	{
		$data              = (array) json_decode($this->input->json->getRaw(), true);
		$data['client_id'] = $this->getClientIdFromInput();

		$this->input->set('data', $data);

		return parent::save($recordKey);
	}

	/**
	 * Get client id from input
	 *
	 * @return string
	 *
	 * @since 4.0
	 */
	private function getClientIdFromInput()
	{
		return $this->input->exists('client_id') ? $this->input->get('client_id') : $this->input->post->get('client_id');
	}
}
