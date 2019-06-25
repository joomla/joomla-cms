<?php
/**
 * @package     Joomla.API
 * @subpackage  com_plugins
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Api\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\Component\Config\Api\View\Component\JsonApiView;
use Joomla\Component\Config\Administrator\Model\ComponentModel;

/**
 * The component controller
 *
 * @since  4.0.0
 */
class ComponentController extends ApiController
{
	/**
	 * The content type of the item.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $contentType = 'component';

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $default_view = 'component';

	/**
	 * Basic display of a list view
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @since   4.0.0
	 */
	public function displayList()
	{
		$viewType = $this->app->getDocument()->getType();
		$viewLayout = $this->input->get('layout', 'default', 'string');

		try
		{
			/** @var JsonApiView $view */
			$view = $this->getView(
				$this->default_view,
				$viewType,
				'',
				['base_path' => $this->basePath, 'layout' => $viewLayout, 'contentType' => $this->contentType]
			);
		}
		catch (\Exception $e)
		{
			return $this;
		}

		/** @var ComponentModel $model */
		$model = $this->getModel($this->contentType);

		if (!$model)
		{
			throw new \RuntimeException('Model failed to be created', 500);
		}

		// Push the model into the view (as default)
		$view->setModel($model, true);
		$view->set('component_name', $this->input->get('component_name'));

		$view->document = $this->app->getDocument();
		$view->displayList();

		return $this;
	}

	/**
	 * Method to edit an existing record.
	 *
	 * @return  boolean  True if save succeeded after access level check and checkout passes, false otherwise.
	 *
	 * @since   4.0.0
	 */
	public function edit()
	{
		/** @var ComponentModel $model */
		$model = $this->getModel($this->contentType);

		if (!$model)
		{
			throw new \RuntimeException('Model failed to be created', 500);
		}

		// Access check.
		if (!$this->allowEdit())
		{
			throw new NotAllowed('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED', 403);
		}

		$option = $this->input->get('component_name');

		// TODO: Not the cleanest thing ever but it works...
		Form::addFormPath(JPATH_ADMINISTRATOR . '/components/' . $option);

		// Must load after serving service-requests
		$form = $model->getForm();

		$data = json_decode($this->input->json->getRaw(), true);

		$component = ComponentHelper::getComponent($option);
		$oldData   = $component->getParams()->toArray();
		$data      = array_replace($oldData, $data);

		// Validate the posted data.
		$return = $model->validate($form, $data);

		if ($return === false)
		{
			throw new \RuntimeException('Invalid input data', 400);
		}

		// Attempt to save the configuration.
		$data = array(
			'params' => $return,
			'id'     => ExtensionHelper::getExtensionRecord($option)->extension_id,
			'option' => $option
		);

		try
		{
			$model->save($data);
		}
		catch (\RuntimeException $e)
		{
			throw new \RuntimeException('Internal server error', 500, $e);
		}

		return true;
	}
}
