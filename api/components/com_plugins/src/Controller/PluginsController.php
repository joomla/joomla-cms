<?php
/**
 * @package     Joomla.API
 * @subpackage  com_plugins
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Plugins\Api\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\CMS\MVC\Controller\Exception;
use Joomla\CMS\Router\Exception\RouteNotFoundException;
use Joomla\String\Inflector;
use Tobscure\JsonApi\Exception\InvalidParameterException;

/**
 * The plugins controller
 *
 * @since  4.0.0
 */
class PluginsController extends ApiController
{
	/**
	 * The content type of the item.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $contentType = 'plugins';

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 *
	 * @since  3.0
	 */
	protected $default_view = 'plugins';

	/**
	 * Method to edit an existing record.
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @since   4.0.0
	 */
	public function edit()
	{
		$recordId = $this->input->getInt('id');

		if (!$recordId)
		{
			throw new Exception\ResourceNotFound(Text::_('JLIB_APPLICATION_ERROR_RECORD'), 404);
		}

		$data = json_decode($this->input->json->getRaw(), true);

		foreach ($data as $key => $value)
		{
			if (!\in_array($key, ['enabled', 'access', 'ordering']))
			{
				throw new InvalidParameterException("Invalid parameter {$key}.", 400);
			}
		}

		/** @var \Joomla\Component\Plugins\Administrator\Model\PluginModel $model */
		$model = $this->getModel(Inflector::singularize($this->contentType), '', ['ignore_request' => true]);

		if (!$model)
		{
			throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_MODEL_CREATE'));
		}

		$item = $model->getItem($recordId);

		if (!isset($item->extension_id))
		{
			throw new RouteNotFoundException('Item does not exist');
		}

		$data['folder']  = $item->folder;
		$data['element'] = $item->element;

		$this->input->set('data', $data);

		return parent::edit();
	}

	/**
	 * Plugin list view with filtering of data
	 *
	 * @return  static  A BaseController object to support chaining.
	 *
	 * @since   4.0.0
	 */
	public function displayList()
	{
		$apiFilterInfo = $this->input->get('filter', [], 'array');
		$filter        = InputFilter::getInstance();

		if (\array_key_exists('element', $apiFilterInfo))
		{
			$this->modelState->set('filter.element', $filter->clean($apiFilterInfo['element'], 'STRING'));
		}

		if (\array_key_exists('status', $apiFilterInfo))
		{
			$this->modelState->set('filter.enabled', $filter->clean($apiFilterInfo['status'], 'INT'));
		}

		if (\array_key_exists('search', $apiFilterInfo))
		{
			$this->modelState->set('filter.search', $filter->clean($apiFilterInfo['search'], 'STRING'));
		}

		if (\array_key_exists('type', $apiFilterInfo))
		{
			$this->modelState->set('filter.folder', $filter->clean($apiFilterInfo['type'], 'STRING'));
		}

		return parent::displayList();
	}
}
