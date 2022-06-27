<?php
/**
 * @package     Joomla.API
 * @subpackage  com_templates
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Templates\Api\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\String\Inflector;
use Tobscure\JsonApi\Exception\InvalidParameterException;

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
		$this->modelState->set('client_id', $this->getClientIdFromInput());

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
		$this->modelState->set('client_id', $this->getClientIdFromInput());

		return parent::displayList();
	}

	/**
	 * Method to allow extended classes to manipulate the data to be saved for an extension.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 * @throws  InvalidParameterException
	 */
	protected function preprocessSaveData(array $data): array
	{
		$data['client_id'] = $this->getClientIdFromInput();

		// If we are updating an item the template is a readonly property based on the ID
		if ($this->input->getMethod() === 'PATCH')
		{
			if (\array_key_exists('template', $data))
			{
				throw new InvalidParameterException('The template property cannot be modified for an existing style');
			}

			$model = $this->getModel(Inflector::singularize($this->contentType), '', ['ignore_request' => true]);
			$data['template'] = $model->getItem($this->input->getInt('id'))->template;
		}

		return $data;
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
		return $this->input->exists('client_id') ? $this->input->get('client_id') : $this->input->post->get('client_id');
	}
}
