<?php
/**
 * @package     Joomla.API
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Api\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Component\Menus\Api\View\Items\JsonApiView;

/**
 * The items controller
 *
 * @since  4.0.0
 */
class ItemsController extends ApiController
{
	/**
	 * The content type of the item.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $contentType = 'items';

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $default_view = 'items';

	/**
	 * Return menu items types
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @since   4.0.0
	 */
	public function getTypes()
	{
		$viewType   = $this->app->getDocument()->getType();
		$viewName   = $this->input->get('view', $this->default_view);
		$viewLayout = $this->input->get('layout', 'default', 'string');

		try
		{
			/** @var JsonApiView $view */
			$view = $this->getView(
				$viewName,
				$viewType,
				'',
				['base_path' => $this->basePath, 'layout' => $viewLayout, 'contentType' => $this->contentType]
			);
		}
		catch (\Exception $e)
		{
			throw new \RuntimeException($e->getMessage());
		}

		/** @var ListModel $model */
		$model = $this->getModel('menutypes', '', ['ignore_request' => true]);

		if (!$model)
		{
			throw new \RuntimeException('Unable to create the model.');
		}

		$clientId = $this->input->exists('client_id') ?
			$this->input->get('client_id') : $this->input->post->get('client_id');

		$model->setState('client_id', $clientId);

		$view->setModel($model, true);

		$view->document = $this->app->getDocument();

		$view->displayListTypes();

		return $this;
	}
}
