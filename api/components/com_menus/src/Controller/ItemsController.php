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

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Component\Menus\Api\View\Items\JsonapiView;

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
	 * Method to add a new record.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 * @throws  NotAllowed
	 * @throws  \RuntimeException
	 */
	public function add()
	{
		$data = $this->input->get('data', json_decode($this->input->json->getRaw(), true), 'array');

		if (isset($data['menutype']))
		{
			$this->input->set('menutype', $data['menutype']);
			$this->input->set('com_menus.items.menutype', $data['menutype']);
		}

		isset($data['type'])      && $this->input->set('type', $data['type']);
		isset($data['parent_id']) && $this->input->set('parent_id', $data['parent_id']);
		isset($data['link'])      && $this->input->set('link', $data['link']);

		$this->input->set('id', '0');

		parent::add();
	}

	/**
	 * Method to edit an existing record.
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @since   4.0.0
	 */
	public function edit()
	{
		$data = $this->input->get('data', json_decode($this->input->json->getRaw(), true), 'array');

		if (isset($data['menutype']))
		{
			$this->input->set('menutype', $data['menutype']);
			$this->input->set('com_menus.items.menutype', $data['menutype']);
		}

		isset($data['type'])      && $this->input->set('type', $data['type']);
		isset($data['parent_id']) && $this->input->set('parent_id', $data['parent_id']);
		isset($data['link'])      && $this->input->set('link', $data['link']);

		return parent::edit();
	}

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
			/** @var JsonapiView $view */
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
			throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_MODEL_CREATE'));
		}

		$model->setState('client_id', $this->getClientIdFromInput());

		$view->setModel($model, true);

		$view->document = $this->app->getDocument();

		$view->displayListTypes();

		return $this;
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
