<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\View\DataView;

defined('_JEXEC') || die;

use Exception;
use FOF30\Container\Container;
use FOF30\Model\DataModel;
use FOF30\Model\DataModel\Collection;
use FOF30\View\View;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Pagination\Pagination;
use Joomla\Registry\Registry;
use stdClass;

/**
 * View for a raw data-driven view
 */
class Raw extends View implements DataViewInterface
{
	/** @var   stdClass  Data lists */
	protected $lists = null;

	/** @var Pagination The pagination object */
	protected $pagination = null;

	/** @var Registry|Registry Page parameters object, for front-end views */
	protected $pageParams = null;

	/** @var Collection The records loaded (browse views) */
	protected $items = null;

	/** @var DataModel The record loaded (read, edit, add views) */
	protected $item = null;

	/** @var int The total number of items in the model (more than those loaded) */
	protected $itemCount = 0;

	/** @var stdClass ACL permissions map */
	protected $permissions = null;

	/** @var array Additional permissions to fetch on object creation, see getPermissions() */
	protected $additionalPermissions = [];

	/**
	 * Overrides the constructor to apply Joomla! ACL permissions
	 *
	 * @param   Container  $container  The container we belong to
	 * @param   array      $config     The configuration overrides for the view
	 */
	public function __construct(Container $container, array $config = [])
	{
		parent::__construct($container, $config);

		$this->permissions = $this->getPermissions(null, $this->additionalPermissions);
	}

	/**
	 * Determines if the current Joomla! version and your current table support AJAX-powered drag and drop reordering.
	 * If they do, it will set up the drag & drop reordering feature.
	 *
	 * @return  boolean|array  False if not supported, otherwise a table with necessary information (saveOrder: should
	 *                           you enable DnD reordering; orderingColumn: which column has the ordering information).
	 */
	public function hasAjaxOrderingSupport()
	{
		/** @var DataModel $model */
		$model = $this->getModel();

		if (!$model->hasField('ordering'))
		{
			return false;
		}

		$listOrder       = $this->escape($model->getState('filter_order', null, 'cmd'));
		$listDirn        = $this->escape($model->getState('filter_order_Dir', null, 'cmd'));
		$saveOrder       = $listOrder == $model->getFieldAlias('ordering');
		$saveOrderingUrl = '';

		if ($saveOrder)
		{
			$saveOrderingUrl = 'index.php?option=' . $this->container->componentName . '&view=' . $this->getName() . '&task=saveorder&format=json';
			$helper          = version_compare(JVERSION, '3.999.999', 'le') ? 'sortablelist.sortable' : 'draggablelist.draggable';

			HtmlHelper::_($helper, 'itemsList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
		}

		return [
			'saveOrder'      => $saveOrder,
			'saveOrderURL'   => $saveOrderingUrl . '&' . $this->container->platform->getToken() . '=1',
			'orderingColumn' => $model->getFieldAlias('ordering'),
		];
	}

	/**
	 * Returns the internal list of useful variables to the benefit of header fields.
	 *
	 * @return stdClass
	 */
	public function getLists()
	{
		return $this->lists;
	}

	/**
	 * Returns a reference to the permissions object of this view
	 *
	 * @return stdClass
	 */
	public function getPerms()
	{
		return $this->permissions;
	}

	/**
	 * Returns a reference to the pagination object of this view
	 *
	 * @return Pagination
	 */
	public function getPagination()
	{
		return $this->pagination;
	}

	/**
	 * Get the items collection for browse views
	 *
	 * @return Collection
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * Get the item for read, edit, add views
	 *
	 * @return DataModel
	 */
	public function getItem()
	{
		return $this->item;
	}

	/**
	 * Get the items count for browse views
	 *
	 * @return int
	 */
	public function getItemCount()
	{
		return $this->itemCount;
	}

	/**
	 * Get the Joomla! page parameters
	 *
	 * @return Registry|Registry
	 */
	public function getPageParams()
	{
		return $this->pageParams;
	}

	/**
	 * Returns a permissions object.
	 *
	 * The additionalPermissions array is a hashed array of local key => Joomla! ACL key value pairs. Local key is the
	 * name of the permission in the permissions object, whereas Joomla! ACL key is the name of the ACL permission
	 * known to Joomla! e.g. "core.manage", "foobar.something" and so on.
	 *
	 * Note: on CLI applications all permissions are set to TRUE. There is no ACL check there.
	 *
	 * @param   null|string  $component              The name of the component. Leave empty for automatic detection.
	 * @param   array        $additionalPermissions  Any additional permissions you want to add to the object.
	 *
	 * @return  object
	 */
	protected function getPermissions($component = null, array $additionalPermissions = [])
	{
		// Make sure we have a component
		if (empty($component))
		{
			$component = $this->container->componentName;
		}

		// Initialise with all true
		$permissions = [
			'create'    => true,
			'edit'      => true,
			'editown'   => true,
			'editstate' => true,
			'delete'    => true,
		];

		if (!empty($additionalPermissions))
		{
			foreach ($additionalPermissions as $localKey => $joomlaPermission)
			{
				$permissions[$localKey] = true;
			}
		}

		$platform = $this->container->platform;

		// If this is a CLI application we don't make any ACL checks
		if ($platform->isCli())
		{
			return (object) $permissions;
		}

		// Get the core permissions
		$permissions = [
			'create'    => $platform->authorise('core.create', $component),
			'edit'      => $platform->authorise('core.edit', $component),
			'editown'   => $platform->authorise('core.edit.own', $component),
			'editstate' => $platform->authorise('core.edit.state', $component),
			'delete'    => $platform->authorise('core.delete', $component),
		];

		if (!empty($additionalPermissions))
		{
			foreach ($additionalPermissions as $localKey => $joomlaPermission)
			{
				$permissions[$localKey] = $platform->authorise($joomlaPermission, $component);
			}
		}

		return (object) $permissions;
	}

	/**
	 * Executes before rendering the page for the Browse task.
	 */
	protected function onBeforeBrowse()
	{
		// Create the lists object
		$this->lists = new stdClass();

		// Load the model
		/** @var DataModel $model */
		$model = $this->getModel();

		// We want to persist the state in the session
		$model->savestate(1);

		// Display limits
		$defaultLimit = 20;

		if (!$this->container->platform->isCli() && class_exists('JFactory'))
		{
			$app = Factory::getApplication();

			if (method_exists($app, 'get'))
			{
				$defaultLimit = $app->get('list_limit');
			}
			else
			{
				$defaultLimit = 20;
			}
		}

		$this->lists->limitStart = $model->getState('limitstart', 0, 'int');
		$this->lists->limit      = $model->getState('limit', $defaultLimit, 'int');

		$model->limitstart = $this->lists->limitStart;
		$model->limit      = $this->lists->limit;

		// Assign items to the view
		$this->items     = $model->get(false);
		$this->itemCount = $model->count();

		// Ordering information
		$this->lists->order     = $model->getState('filter_order', $model->getIdFieldName(), 'cmd');
		$this->lists->order_Dir = $model->getState('filter_order_Dir', null, 'cmd');

		if ($this->lists->order_Dir)
		{
			$this->lists->order_Dir = strtolower($this->lists->order_Dir);
		}

		// Pagination
		$this->pagination = new Pagination($this->itemCount, $this->lists->limitStart, $this->lists->limit);

		// Pass page params on frontend only
		if ($this->container->platform->isFrontend())
		{
			/** @var SiteApplication $app */
			$app              = Factory::getApplication();
			$params           = $app->getParams();
			$this->pageParams = $params;
		}
	}

	/**
	 * Executes before rendering the page for the add task.
	 */
	protected function onBeforeAdd()
	{
		/** @var DataModel $model */
		$model = $this->getModel();

		/**
		 * The model is pushed into the View by the Controller. As you can see in DataController::add() it is possible
		 * to push both default values (defaultsForAdd) as well as data from the state (e.g. when saving a new record
		 * failed for some reason and the user needs to edit it). That's why we populate defaultFields from $model. We
		 * still do a full reset on a clone of the Model to get a clean object and merge default values (instead of null
		 * values) with the data pushed by the controller.
		 */
		$defaultFields = $model->getData();
		$this->item    = $model->getClone()->reset(true, true);

		foreach ($defaultFields as $k => $v)
		{
			try
			{
				$this->item->setFieldValue($k, $v);
			}
			catch (Exception $e)
			{
				// Suppress errors in field assignments at this stage
			}
		}
	}

	/**
	 * Executes before rendering the page for the Edit task.
	 */
	protected function onBeforeEdit()
	{
		/** @var DataModel $model */
		$model = $this->getModel();

		// It seems that I can't edit records, maybe I can edit only this one due asset tracking?
		if (!$this->permissions->edit || !$this->permissions->editown)
		{
			if ($model)
			{
				// Ok, record is tracked, let's see if I can this record
				if ($model->isAssetsTracked())
				{
					$platform = $this->container->platform;

					if (!$this->permissions->edit)
					{
						$this->permissions->edit = $platform->authorise('core.edit', $model->getAssetName());
					}

					if (!$this->permissions->editown)
					{
						$this->permissions->editown = $platform->authorise('core.edit.own', $model->getAssetName());
					}
				}
			}
		}

		$this->item = $model->findOrFail();
	}

	/**
	 * Executes before rendering the page for the Read task.
	 */
	protected function onBeforeRead()
	{
		/** @var DataModel $model */
		$model = $this->getModel();

		$this->item = $model->findOrFail();
	}
}
