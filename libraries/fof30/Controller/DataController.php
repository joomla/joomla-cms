<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Controller;

defined('_JEXEC') || die;

use FOF30\Container\Container;
use FOF30\Controller\Exception\ItemNotFound;
use FOF30\Controller\Exception\LockedRecord;
use FOF30\Controller\Exception\NotADataModel;
use FOF30\Controller\Exception\TaskNotFound;
use FOF30\Model\DataModel;
use FOF30\View\View;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Response\JsonResponse;

/**
 * Database-aware Controller
 *
 * @property-read  \FOF30\Input\Input $input  The input object (magic __get returns the Input from the Container)
 */
class DataController extends Controller
{
	/**
	 * Variables that should be taken in account while working with the cache. You can set them in Controller
	 * constructor or inside onBefore* methods
	 *
	 * @var false|array
	 */
	protected $cacheParams = false;

	/**
	 * An associative array for required ACL privileges per task. For example:
	 * array(
	 *   'edit' => 'core.edit',
	 *   'jump' => 'foobar.jump',
	 *   'alwaysallow' => 'true',
	 *   'neverallow' => 'false'
	 * );
	 *
	 * You can use the notation '@task' which means 'apply the same privileges as "task"'. If you create a reference
	 * back to yourself (e.g. 'mytask' => array('@mytask')) it will return TRUE.
	 *
	 * @var array
	 */
	protected $taskPrivileges = [
		// Special privileges
		'*editown'    => 'core.edit.own', // Privilege required to edit own record
		// Standard tasks
		'add'         => 'core.create',
		'apply'       => '&getACLForApplySave', // Apply task: call the getACLForApplySave method
		'archive'     => 'core.edit.state',
		'cancel'      => 'core.edit.state',
		'copy'        => '@add', // Maps copy ACLs to the add task
		'edit'        => 'core.edit',
		'loadhistory' => '@edit', // Maps loadhistory ACLs to the edit task
		'orderup'     => 'core.edit.state',
		'orderdown'   => 'core.edit.state',
		'publish'     => 'core.edit.state',
		'remove'      => 'core.delete',
		'forceRemove' => 'core.delete',
		'save'        => '&getACLForApplySave', // Save task: call the getACLForApplySave method
		'savenew'     => 'core.create',
		'saveorder'   => 'core.edit.state',
		'trash'       => 'core.edit.state',
		'unpublish'   => 'core.edit.state',
	];

	/**
	 * An indexed array of default values for the add task. Since the add task resets the model you can't set these
	 * values directly to the model. Instead, the defaultsForAdd values will be fed to model's bind() after it's reset
	 * and before the session-stored item data is bound to the model object.
	 *
	 * @var  array
	 */
	protected $defaultsForAdd = [];

	/**
	 * Public constructor of the Controller class. You can pass the following variables in the $config array,
	 * on top of what you already have in the base Controller class:
	 *
	 * taskPrivileges       array   ACL privileges for each task
	 * cacheableTasks       array   The cache-enabled tasks
	 *
	 * @param   Container  $container  The application container
	 * @param   array      $config     The configuration array
	 */
	public function __construct(Container $container, array $config = [])
	{
		parent::__construct($container, $config);

		// Set up a default model name if none is provided
		if (empty($this->modelName))
		{
			$this->modelName = $container->inflector->pluralize($this->view);
		}

		// Set up a default view name if none is provided
		if (empty($this->viewName))
		{
			$this->viewName = $container->inflector->pluralize($this->view);
		}

		if (isset($config['cacheableTasks']))
		{
			if (!is_array($config['cacheableTasks']))
			{
				$config['cacheableTasks'] = explode(',', $config['cacheableTasks']);
				$config['cacheableTasks'] = array_map('trim', $config['cacheableTasks']);
			}

			$this->cacheableTasks = $config['cacheableTasks'];
		}
		elseif ($this->container->platform->isBackend())
		{
			$this->cacheableTasks = [];
		}
		else
		{
			$this->cacheableTasks = ['browse', 'read'];
		}

		if (isset($config['taskPrivileges']) && is_array($config['taskPrivileges']))
		{
			$this->taskPrivileges = array_merge($this->taskPrivileges, $config['taskPrivileges']);
		}
	}

	/**
	 * Executes a given controller task. The onBefore<task> and onAfter<task> methods are called automatically if they
	 * exist.
	 *
	 * If $task == 'default' we will determine the CRUD task to use based on the view name and HTTP verb in the request,
	 * overriding the routing.
	 *
	 * @param   string  $task  The task to execute, e.g. "browse"
	 *
	 * @return  null|bool  False on execution failure
	 *
	 * @throws  TaskNotFound  When the task is not found
	 */
	public function execute($task)
	{
		if ($task == 'default')
		{
			$task = $this->getCrudTask();
		}

		return parent::execute($task);
	}

	/**
	 * Returns a named View object
	 *
	 * @param   string  $name    The Model name. If null we'll use the modelName
	 *                           variable or, if it's empty, the same name as
	 *                           the Controller
	 * @param   array   $config  Configuration parameters to the Model. If skipped
	 *                           we will use $this->config
	 *
	 * @return  View  The instance of the Model known to this Controller
	 */
	public function getView($name = null, $config = [])
	{
		if (!empty($name))
		{
			$viewName = $name;
		}
		elseif (!empty($this->viewName))
		{
			$viewName = $this->viewName;
		}
		else
		{
			$viewName = $this->view;
		}

		if (!array_key_exists($viewName, $this->viewInstances))
		{
			if (empty($config) && isset($this->config['viewConfig']))
			{
				$config = $this->config['viewConfig'];
			}

			$viewType = $this->input->getCmd('format', 'html');

			// Get the model's class name
			$this->viewInstances[$viewName] = $this->container->factory->view($viewName, $viewType, $config);
		}

		return $this->viewInstances[$viewName];
	}

	/**
	 * Implements a default browse task, i.e. read a bunch of records and send
	 * them to the browser.
	 *
	 * @return  void
	 */
	public function browse()
	{
		// Initialise the savestate
		$saveState = $this->input->get('savestate', -999, 'int');

		if ($saveState == -999)
		{
			$saveState = true;
		}

		$this->getModel()->savestate($saveState);

		// Display the view
		$this->display(in_array('browse', $this->cacheableTasks), $this->cacheParams);
	}

	/**
	 * Single record read. The id set in the request is passed to the model and
	 * then the item layout is used to render the result.
	 *
	 * @return  void
	 *
	 * @throws ItemNotFound When the item is not found
	 */
	public function read()
	{
		// Load the model
		/** @var DataModel $model */
		$model = $this->getModel()->savestate(false);

		// If there is no record loaded, try loading a record based on the id passed in the input object
		if (!$model->getId())
		{
			$ids = $this->getIDsFromRequest($model, true);

			if ($model->getId() != reset($ids))
			{
				$key = strtoupper($this->container->componentName . '_ERR_' . $model->getName() . '_NOTFOUND');
				throw new ItemNotFound(Text::_($key), 404);
			}
		}

		// Set the layout to item, if it's not set in the URL
		if (empty($this->layout))
		{
			$this->layout = 'item';
		}
		elseif ($this->layout == 'default')
		{
			$this->layout = 'item';
		}

		// Display the view
		$this->display(in_array('read', $this->cacheableTasks), $this->cacheParams);
	}

	/**
	 * Single record add. The form layout is used to present a blank page.
	 *
	 * @return  void
	 */
	public function add()
	{
		// Load and reset the model
		$model = $this->getModel()->savestate(false);
		$model->reset();

		// Set the layout to form, if it's not set in the URL
		if (empty($this->layout))
		{
			$this->layout = 'form';
		}
		elseif ($this->layout == 'default')
		{
			$this->layout = 'form';
		}

		if (!empty($this->defaultsForAdd))
		{
			$model->bind($this->defaultsForAdd);
		}

		// Get temporary data from the session, set if the save failed and we're redirected back here
		$sessionKey = $this->viewName . '.savedata';
		$itemData   = $this->container->platform->getSessionVar($sessionKey, null, $this->container->componentName);
		$this->container->platform->setSessionVar($sessionKey, null, $this->container->componentName);

		if (!empty($itemData))
		{
			$model->bind($itemData);
		}

		// Display the view
		$this->display(in_array('add', $this->cacheableTasks), $this->cacheParams);
	}

	/**
	 * Single record edit. The ID set in the request is passed to the model,
	 * then the form layout is used to edit the result.
	 *
	 * @return  void
	 */
	public function edit()
	{
		// Load the model
		/** @var DataModel $model */
		$model = $this->getModel()->savestate(false);

		if (!$model->getId())
		{
			$this->getIDsFromRequest($model, true);
		}

		$userId = $this->container->platform->getUser()->id;

		try
		{
			if ($model->isLocked($userId))
			{
				$model->checkIn($userId);
			}

			$model->lock();
		}
		catch (\Exception $e)
		{
			// Redirect on error
			if ($customURL = $this->input->getBase64('returnurl', ''))
			{
				$customURL = base64_decode($customURL);
			}

			$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->container->componentName . '&view=' . $this->container->inflector->pluralize($this->view) . $this->getItemidURLSuffix();
			$this->setRedirect($url, $e->getMessage(), 'error');

			return;
		}

		// Set the layout to form, if it's not set in the URL
		if (empty($this->layout))
		{
			$this->layout = 'form';
		}
		elseif ($this->layout == 'default')
		{
			$this->layout = 'form';
		}

		// Get temporary data from the session, set if the save failed and we're redirected back here
		$sessionKey = $this->viewName . '.savedata';
		$itemData   = $this->container->platform->getSessionVar($sessionKey, null, $this->container->componentName);
		$this->container->platform->setSessionVar($sessionKey, null, $this->container->componentName);

		if (!empty($itemData))
		{
			$model->bind($itemData);
		}

		// Display the view
		$this->display(in_array('edit', $this->cacheableTasks), $this->cacheParams);
	}

	/**
	 * Save the incoming data and then return to the Edit task
	 *
	 * @return  void
	 */
	public function apply()
	{
		// CSRF prevention
		$this->csrfProtection();

		// Redirect to the edit task
		if (!$this->applySave())
		{
			return;
		}

		$id      = $this->input->get('id', 0, 'int');
		$textKey = strtoupper($this->container->componentName . '_LBL_' . $this->container->inflector->singularize($this->view) . '_SAVED');

		if ($customURL = $this->input->getBase64('returnurl', ''))
		{
			$customURL = base64_decode($customURL);
		}

		$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->container->componentName . '&view=' . $this->view . '&task=edit&id=' . $id . $this->getItemidURLSuffix();
		$this->setRedirect($url, Text::_($textKey));
	}

	/**
	 * Duplicates selected items
	 *
	 * @return  void
	 */
	public function copy()
	{
		// CSRF prevention
		$this->csrfProtection();

		$model = $this->getModel()->savestate(false);

		$ids = $this->getIDsFromRequest($model, true);

		$error = null;

		try
		{
			$status = true;

			foreach ($ids as $id)
			{
				$model->find($id);
				$model->copy();
			}
		}
		catch (\Exception $e)
		{
			$status = false;
			$error  = $e->getMessage();
		}

		// Redirect
		if ($customURL = $this->input->getBase64('returnurl', ''))
		{
			$customURL = base64_decode($customURL);
		}

		$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->container->componentName . '&view=' . $this->container->inflector->pluralize($this->view) . $this->getItemidURLSuffix();

		if (!$status)
		{
			$this->setRedirect($url, $error, 'error');
		}
		else
		{
			$textKey = strtoupper($this->container->componentName . '_LBL_' . $this->container->inflector->singularize($this->view) . '_COPIED');
			$this->setRedirect($url, Text::_($textKey));
		}
	}

	/**
	 * Save the incoming data and then return to the Browse task
	 *
	 * @return  void
	 */
	public function save()
	{
		// CSRF prevention
		$this->csrfProtection();

		if (!$this->applySave())
		{
			return;
		}

		$textKey = strtoupper($this->container->componentName . '_LBL_' . $this->container->inflector->singularize($this->view) . '_SAVED');

		if ($customURL = $this->input->getBase64('returnurl', ''))
		{
			$customURL = base64_decode($customURL);
		}

		$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->container->componentName . '&view=' . $this->container->inflector->pluralize($this->view) . $this->getItemidURLSuffix();
		$this->setRedirect($url, Text::_($textKey));
	}

	/**
	 * Save the incoming data and then return to the Add task
	 *
	 * @return  bool
	 */
	public function savenew()
	{
		// CSRF prevention
		$this->csrfProtection();

		if (!$this->applySave())
		{
			return;
		}

		$textKey = strtoupper($this->container->componentName . '_LBL_' . $this->container->inflector->singularize($this->view) . '_SAVED');

		if ($customURL = $this->input->getBase64('returnurl', ''))
		{
			$customURL = base64_decode($customURL);
		}

		$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->container->componentName . '&view=' . $this->container->inflector->singularize($this->view) . '&task=add' . $this->getItemidURLSuffix();
		$this->setRedirect($url, Text::_($textKey));
	}

	/**
	 * Save the incoming data as a copy of the given model and then redirect to the copied object edit view
	 *
	 * @return  bool
	 */
	public function save2copy()
	{
		// CSRF prevention
		$this->csrfProtection();

		$model = $this->getModel()->savestate(false);
		$ids   = $this->getIDsFromRequest($model, true);
		$data  = $this->input->getData();

		unset($data[$model->getIdFieldName()]);

		$error = null;

		try
		{
			$status = true;

			foreach ($ids as $id)
			{
				$model->find($id);
				$model = $model->copy($data);
			}
		}
		catch (\Exception $e)
		{
			$status = false;
			$error  = $e->getMessage();
		}

		// Redirect
		if ($customURL = $this->input->getBase64('returnurl', ''))
		{
			$customURL = base64_decode($customURL);
		}

		$url = !empty($customURL) ? $customURL : $url = 'index.php?option=' . $this->container->componentName . '&view=' . $this->view . '&task=edit&id=' . $model->getId() . $this->getItemidURLSuffix();

		if (!$status)
		{
			$this->setRedirect($url, $error, 'error');
		}
		else
		{
			$textKey = strtoupper($this->container->componentName . '_LBL_' . $this->container->inflector->singularize($this->view) . '_COPIED');
			$this->setRedirect($url, Text::_($textKey));
		}
	}

	/**
	 * Cancel the edit, check in the record and return to the Browse task
	 *
	 * @return  void
	 */
	public function cancel()
	{
		$model = $this->getModel()->tmpInstance()->savestate(false);

		if (!$model->getId())
		{
			$this->getIDsFromRequest($model, true);
		}

		if ($model->getId())
		{
			$userId = $this->container->platform->getUser()->id;

			if ($model->isLocked($userId))
			{
				try
				{
					$model->checkIn($userId);
				}
				catch (LockedRecord $e)
				{
					// Redirect to the display task
					if ($customURL = $this->input->getBase64('returnurl', ''))
					{
						$customURL = base64_decode($customURL);
					}

					$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->container->componentName . '&view=' . $this->container->inflector->pluralize($this->view) . $this->getItemidURLSuffix();
					$this->setRedirect($url, $e->getMessage(), 'error');
				}
			}

			$model->unlock();
		}

		// Remove any saved data
		$sessionKey = $this->viewName . '.savedata';
		$this->container->platform->setSessionVar($sessionKey, null, $this->container->componentName);

		// Redirect to the display task
		if ($customURL = $this->input->getBase64('returnurl', ''))
		{
			$customURL = base64_decode($customURL);
		}

		$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->container->componentName . '&view=' . $this->container->inflector->pluralize($this->view) . $this->getItemidURLSuffix();
		$this->setRedirect($url);
	}

	/**
	 * Publish (set enabled = 1) an item.
	 *
	 * @return  void
	 */
	public function publish()
	{
		// CSRF prevention
		$this->csrfProtection();

		$model = $this->getModel()->savestate(false);
		$ids   = $this->getIDsFromRequest($model, false);
		$error = false;

		try
		{
			$status = true;

			foreach ($ids as $id)
			{
				$model->find($id);

				$userId = $this->container->platform->getUser()->id;

				if ($model->isLocked($userId))
				{
					$model->checkIn($userId);
				}

				$model->publish();
			}
		}
		catch (\Exception $e)
		{
			$status = false;
			$error  = $e->getMessage();
		}

		// Redirect
		if ($customURL = $this->input->getBase64('returnurl', ''))
		{
			$customURL = base64_decode($customURL);
		}

		$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->container->componentName . '&view=' . $this->container->inflector->pluralize($this->view) . $this->getItemidURLSuffix();

		if (!$status)
		{
			$this->setRedirect($url, $error, 'error');
		}
		else
		{
			$this->setRedirect($url);
		}
	}

	/**
	 * Unpublish (set enabled = 0) an item.
	 *
	 * @return  void
	 */
	public function unpublish()
	{
		// CSRF prevention
		$this->csrfProtection();

		$model = $this->getModel()->savestate(false);
		$ids   = $this->getIDsFromRequest($model, false);
		$error = null;

		try
		{
			$status = true;

			foreach ($ids as $id)
			{
				$model->find($id);

				$userId = $this->container->platform->getUser()->id;

				if ($model->isLocked($userId))
				{
					$model->checkIn($userId);
				}

				$model->unpublish();
			}
		}
		catch (\Exception $e)
		{
			$status = false;
			$error  = $e->getMessage();
		}

		// Redirect
		if ($customURL = $this->input->getBase64('returnurl', ''))
		{
			$customURL = base64_decode($customURL);
		}

		$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->container->componentName . '&view=' . $this->container->inflector->pluralize($this->view) . $this->getItemidURLSuffix();

		if (!$status)
		{
			$this->setRedirect($url, $error, 'error');
		}
		else
		{
			$this->setRedirect($url);
		}
	}

	/**
	 * Archive (set enabled = 2) an item.
	 *
	 * @return  void
	 */
	public function archive()
	{
		// CSRF prevention
		$this->csrfProtection();

		$model = $this->getModel()->savestate(false);
		$ids   = $this->getIDsFromRequest($model, false);
		$error = null;

		try
		{
			$status = true;

			foreach ($ids as $id)
			{
				$model->find($id);

				$userId = $this->container->platform->getUser()->id;

				if ($model->isLocked($userId))
				{
					$model->checkIn($userId);
				}

				$model->archive();
			}
		}
		catch (\Exception $e)
		{
			$status = false;
			$error  = $e->getMessage();
		}

		// Redirect
		if ($customURL = $this->input->getBase64('returnurl', ''))
		{
			$customURL = base64_decode($customURL);
		}

		$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->container->componentName . '&view=' . $this->container->inflector->pluralize($this->view) . $this->getItemidURLSuffix();

		if (!$status)
		{
			$this->setRedirect($url, $error, 'error');
		}
		else
		{
			$this->setRedirect($url);
		}
	}

	/**
	 * Trash (set enabled = -2) an item.
	 *
	 * @return  void
	 */
	public function trash()
	{
		// CSRF prevention
		$this->csrfProtection();

		$model = $this->getModel()->savestate(false);
		$ids   = $this->getIDsFromRequest($model, false);
		$error = null;

		try
		{
			$status = true;

			foreach ($ids as $id)
			{
				$model->find($id);

				$userId = $this->container->platform->getUser()->id;

				if ($model->isLocked($userId))
				{
					$model->checkIn($userId);
				}

				$model->trash();
			}
		}
		catch (\Exception $e)
		{
			$status = false;
			$error  = $e->getMessage();
		}

		// Redirect
		if ($customURL = $this->input->getBase64('returnurl', ''))
		{
			$customURL = base64_decode($customURL);
		}

		$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->container->componentName . '&view=' . $this->container->inflector->pluralize($this->view) . $this->getItemidURLSuffix();

		if (!$status)
		{
			$this->setRedirect($url, $error, 'error');
		}
		else
		{
			$this->setRedirect($url);
		}
	}

	/**
	 * Check in (unlock) items
	 *
	 * @return  void
	 */
	public function checkin()
	{
		// CSRF prevention
		$this->csrfProtection();

		$model = $this->getModel()->savestate(false);
		$ids   = $this->getIDsFromRequest($model, false);
		$error = null;

		try
		{
			$status = true;

			foreach ($ids as $id)
			{
				$model->find($id);
				$model->checkIn();
			}
		}
		catch (\Exception $e)
		{
			$status = false;
			$error  = $e->getMessage();
		}

		// Redirect
		if ($customURL = $this->input->getBase64('returnurl', ''))
		{
			$customURL = base64_decode($customURL);
		}

		$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->container->componentName . '&view=' . $this->container->inflector->pluralize($this->view) . $this->getItemidURLSuffix();

		if (!$status)
		{
			$this->setRedirect($url, $error, 'error');
		}
		else
		{
			$this->setRedirect($url);
		}
	}

	/**
	 * Saves the order of the items
	 *
	 * @return  void
	 */
	public function saveorder()
	{
		// CSRF prevention
		$this->csrfProtection();

		$type   = null;
		$msg    = null;
		$model  = $this->getModel()->savestate(false);
		$ids    = $this->getIDsFromRequest($model, false);
		$orders = $this->input->get('order', [], 'array');

		// Before saving the order, I have to check I the table really supports the ordering feature
		if (!$model->hasField('ordering'))
		{
			$msg  = sprintf('%s does not support ordering.', $model->getTableName());
			$type = 'error';
		}
		else
		{
			$ordering = $model->getFieldAlias('ordering');

			// Several methods could throw exceptions, so let's wrap everything in a try-catch
			try
			{
				if ($n = count($ids))
				{
					for ($i = 0; $i < $n; $i++)
					{
						$item     = $model->find($ids[$i]);
						$neworder = (int) $orders[$i];

						if (!($item instanceof DataModel))
						{
							continue;
						}

						if ($item->getId() == $ids[$i])
						{
							$item->$ordering = $neworder;

							$userId = $this->container->platform->getUser()->id;

							if ($model->isLocked($userId))
							{
								$model->checkIn($userId);
							}

							$model->save($item);
						}
					}
				}

				$model->reorder();
			}
			catch (\Exception $e)
			{
				$msg  = $e->getMessage();
				$type = 'error';
			}
		}

		// Redirect
		if ($customURL = $this->input->getBase64('returnurl', ''))
		{
			$customURL = base64_decode($customURL);
		}

		$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->container->componentName . '&view=' . $this->container->inflector->pluralize($this->view) . $this->getItemidURLSuffix();

		$this->setRedirect($url, $msg, $type);
	}

	/**
	 * Moves selected items one position down the ordering list
	 *
	 * @return  void
	 */
	public function orderdown()
	{
		// CSRF prevention
		$this->csrfProtection();

		$model = $this->getModel()->savestate(false);

		if (!$model->getId())
		{
			$this->getIDsFromRequest($model, true);
		}

		$error = null;

		try
		{
			$userId = $this->container->platform->getUser()->id;

			if ($model->isLocked($userId))
			{
				$model->checkIn($userId);
			}

			$model->move(1);
			$status = true;
		}
		catch (\Exception $e)
		{
			$status = false;
			$error  = $e->getMessage();
		}

		// Redirect
		if ($customURL = $this->input->getBase64('returnurl', ''))
		{
			$customURL = base64_decode($customURL);
		}

		$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->container->componentName . '&view=' . $this->container->inflector->pluralize($this->view) . $this->getItemidURLSuffix();

		if (!$status)
		{
			$this->setRedirect($url, $error, 'error');
		}
		else
		{
			$this->setRedirect($url);
		}
	}

	/**
	 * Moves selected items one position up the ordering list
	 *
	 * @return  void
	 */
	public function orderup()
	{
		// CSRF prevention
		$this->csrfProtection();

		$model = $this->getModel()->savestate(false);

		if (!$model->getId())
		{
			$this->getIDsFromRequest($model, true);
		}

		$error = null;

		try
		{
			$userId = $this->container->platform->getUser()->id;

			if ($model->isLocked($userId))
			{
				$model->checkIn($userId);
			}

			$model->move(-1);
			$status = true;
		}
		catch (\Exception $e)
		{
			$status = false;
			$error  = $e->getMessage();
		}

		// Redirect
		if ($customURL = $this->input->getBase64('returnurl', ''))
		{
			$customURL = base64_decode($customURL);
		}

		$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->container->componentName . '&view=' . $this->container->inflector->pluralize($this->view) . $this->getItemidURLSuffix();

		if (!$status)
		{
			$this->setRedirect($url, $error, 'error');
		}
		else
		{
			$this->setRedirect($url);
		}
	}

	/**
	 * Delete or trash selected item(s). The model's softDelete flag determines if the items should be trashed (enabled
	 * state changed to -2) or deleted (completely removed from database)
	 *
	 * @return  void
	 */
	public function remove()
	{
		$this->deleteOrTrash(false);
	}

	/**
	 * Deletes the selected item(s). Unlike remove() this method will force delete the record (completely removed from
	 * database)
	 *
	 * @return  void
	 */
	public function forceRemove()
	{
		$this->deleteOrTrash(true);
	}

	/**
	 * Returns a named Model object. Makes sure that the Model is a database-aware model, throwing an exception
	 * otherwise, when $name is null.
	 *
	 * @param   string  $name    The Model name. If null we'll use the modelName
	 *                           variable or, if it's empty, the same name as
	 *                           the Controller
	 * @param   array   $config  Configuration parameters to the Model. If skipped
	 *                           we will use $this->config
	 *
	 * @return  DataModel  The instance of the Model known to this Controller
	 *
	 * @throws  NotADataModel  When the model type doesn't match our expectations
	 */
	public function getModel($name = null, $config = [])
	{
		$model = parent::getModel($name, $config);

		if (is_null($name) && !($model instanceof DataModel))
		{
			throw new NotADataModel('Model ' . get_class($model) . ' is not a database-aware Model');
		}

		return $model;
	}

	/**
	 * Gets the list of IDs from the request data
	 *
	 * @param   DataModel  $model       The model where the record will be loaded
	 * @param   bool       $loadRecord  When true, the record matching the *first* ID found will be loaded into $model
	 *
	 * @return array
	 */
	public function getIDsFromRequest(DataModel &$model, $loadRecord = true)
	{
		// Get the ID or list of IDs from the request or the configuration
		$cid = $this->input->get('cid', [], 'array');
		$id  = $this->input->getInt('id', 0);
		$kid = $this->input->getInt($model->getIdFieldName(), 0);

		$ids = [];

		if (is_array($cid) && !empty($cid))
		{
			$ids = $cid;
		}
		else
		{
			if (empty($id))
			{
				if (!empty($kid))
				{
					$ids = [$kid];
				}
			}
			else
			{
				$ids = [$id];
			}
		}

		if ($loadRecord && !empty($ids))
		{
			$id = reset($ids);
			$model->find(['id' => $id]);
		}

		return $ids;
	}

	/**
	 * Method to load a row from version history
	 *
	 * @return   boolean  True if the content history is reverted, false otherwise
	 *
	 * @since   2.2
	 */
	public function loadhistory()
	{
		$model = $this->getModel();
		$model->lock();

		$historyId = $this->input->get('version_id', null, 'integer');
		$alias     = $this->container->componentName . '.' . $this->view;
		$returnUrl = 'index.php?option=' . $this->container->componentName . '&view=' . $this->container->inflector->pluralize($this->view) . $this->getItemidURLSuffix();

		if ($customURL = $this->input->getBase64('returnurl', ''))
		{
			$customURL = base64_decode($customURL);
		}

		if (!empty($customURL))
		{
			$returnUrl = $customURL;
		}

		try
		{
			$model->loadhistory($historyId, $alias);
		}
		catch (\Exception $e)
		{
			$this->setRedirect($returnUrl, $e->getMessage(), 'error');
			$model->unlock();

			return false;
		}

		// Access check.
		if (!$this->checkACL('@loadhistory'))
		{
			$this->setRedirect($returnUrl, Text::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'), 'error');
			$model->unlock();

			return false;
		}

		$model->store();
		$this->setRedirect($returnUrl, Text::sprintf('JLIB_APPLICATION_SUCCESS_LOAD_HISTORY', $model->getState('save_date'), $model->getState('version_note')));

		return true;
	}

	/**
	 * Gets a URL suffix with the Itemid parameter. If it's not the front-end of the site, or if
	 * there is no Itemid set it returns an empty string.
	 *
	 * @return  string  The &Itemid=123 URL suffix, or an empty string if Itemid is not applicable
	 */
	public function getItemidURLSuffix()
	{
		if ($this->container->platform->isFrontend() && ($this->input->getCmd('Itemid', 0) != 0))
		{
			return '&Itemid=' . $this->input->getInt('Itemid', 0);
		}
		else
		{
			return '';
		}
	}

	/**
	 * Deal with JSON format: no redirects needed
	 *
	 * @param   string  $task  The task being executed
	 *
	 * @return boolean      True if everything went well
	 */
	protected function onAfterExecute($task)
	{
		// JSON shouldn't have redirects
		if ($this->hasRedirect() && $this->input->getCmd('format', 'html') == 'json')
		{
			// Error: deal with it in REST api way
			if ($this->messageType == 'error')
			{
				$response = new JsonResponse($this->message, $this->message, true);

				echo $response;

				$this->redirect = false;
				$this->container->platform->setHeader('Status', 500);

				return;
			}
			else
			{
				// Not an error, avoid redirect and display the record(s)
				$this->redirect = false;

				return $this->display();
			}
		}

		return true;
	}

	/**
	 * Determines the CRUD task to use based on the view name and HTTP verb used in the request.
	 *
	 * @return  string  The CRUD task (browse, read, edit, delete)
	 */
	protected function getCrudTask()
	{
		// By default, a plural view means 'browse' and a singular view means 'edit'
		$view = $this->input->getCmd('view', null);
		$task = $this->container->inflector->isPlural($view) ? 'browse' : 'edit';

		// If the task is 'edit' but there's no logged in user switch to a 'read' task
		if (($task == 'edit') && !$this->container->platform->getUser()->id)
		{
			$task = 'read';
		}

		// Check if there is an id passed in the request
		$id = $this->input->get('id', null, 'int');

		if ($id == 0)
		{
			$ids = $this->input->get('ids', [], 'array');

			if (!empty($ids))
			{
				$id = array_shift($ids);
			}
		}

		// Get the request HTTP verb
		$requestMethod = 'GET';

		if (isset($_SERVER['REQUEST_METHOD']))
		{
			$requestMethod = strtoupper($_SERVER['REQUEST_METHOD']);
		}

		// Alter the task based on the verb
		switch ($requestMethod)
		{
			// POST and PUT result in a record being saved; no ID means creating a new record
			case 'POST':
			case 'PUT':
				$task = 'save';
				break;

			// DELETE results in a record being deleted, as long as there is an ID
			case 'DELETE':
				if ($id)
				{
					$task = 'remove';
				}
				break;

			// GET results in browse, edit or add depending on the ID
			case 'GET':
			default:
				// If it's an edit without an ID or ID=0, it's really an add
				if (($task == 'edit') && ($id == 0))
				{
					$task = 'add';
				}
				break;
		}

		return $task;
	}

	/**
	 * Checks if the current user has enough privileges for the requested ACL area. This overridden method supports
	 * asset tracking as well.
	 *
	 * @param   string  $area  The ACL area, e.g. core.manage
	 *
	 * @return  boolean  True if the user has the ACL privilege specified
	 */
	protected function checkACL($area)
	{
		$area = $this->getACLRuleFor($area);

		$result = parent::checkACL($area);

		// Check if we're dealing with ids
		$ids = null;

		// First, check if there is an asset for this record
		/** @var DataModel $model */
		$model = $this->getModel();

		$ids = null;

		if (is_object($model) && ($model instanceof DataModel) && $model->isAssetsTracked())
		{
			$ids = $this->getIDsFromRequest($model, false);
		}

		// No IDs tracked, return parent's result
		if (empty($ids))
		{
			return $result;
		}

		// Asset tracking
		if (!is_array($ids))
		{
			$ids = [$ids];
		}

		$resource    = $this->container->inflector->singularize($this->view);
		$isEditState = ($area == 'core.edit.state');

		foreach ($ids as $id)
		{
			$asset = $this->container->componentName . '.' . $resource . '.' . $id;

			// Dedicated permission found, check it!
			$platform = $this->container->platform;

			if ($platform->authorise($area, $asset))
			{
				return true;
			}

			// Fallback on edit.own, if not edit.state. First test if the permission is available.

			$editOwn = $this->getACLRuleFor('@*editown');

			if ((!$isEditState) && ($platform->authorise($editOwn, $asset)))
			{
				$model->load($id);

				if (!$model->hasField('created_by'))
				{
					return false;
				}

				// Now test the owner is the user.
				$owner_id = (int) $model->getFieldValue('created_by', null);

				// If the owner matches 'me' then do the test.
				if ($owner_id == $platform->getUser()->id)
				{
					return true;
				}

				return false;
			}
		}

		// No result found? Not authorised.
		return false;
	}

	protected function deleteOrTrash($forceDelete = false)
	{
		// CSRF prevention
		$this->csrfProtection();

		$model = $this->getModel()->savestate(false);
		$ids   = $this->getIDsFromRequest($model, false);
		$error = null;

		try
		{
			$status = true;

			foreach ($ids as $id)
			{
				$model->find($id);

				$userId = $this->container->platform->getUser()->id;

				if ($model->isLocked($userId))
				{
					$model->checkIn($userId);
				}

				if ($forceDelete)
				{
					$model->forceDelete();
				}
				else
				{
					$model->delete();
				}
			}
		}
		catch (\Exception $e)
		{
			$status = false;
			$error  = $e->getMessage();
		}

		// Redirect
		if ($customURL = $this->input->getBase64('returnurl', ''))
		{
			$customURL = base64_decode($customURL);
		}

		$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->container->componentName . '&view=' . $this->container->inflector->pluralize($this->view) . $this->getItemidURLSuffix();

		if (!$status)
		{
			$this->setRedirect($url, $error, 'error');
		}
		else
		{
			$textKey = strtoupper($this->container->componentName . '_LBL_' . $this->container->inflector->singularize($this->view) . '_DELETED');
			$this->setRedirect($url, Text::_($textKey));
		}
	}

	/**
	 * Common method to handle apply and save tasks
	 *
	 * @return  bool True on success
	 */
	protected function applySave()
	{
		// Load the model
		$model = $this->getModel()->savestate(false);

		if (!$model->getId())
		{
			$this->getIDsFromRequest($model, true);
		}

		$userId = $this->container->platform->getUser()->id;
		$id     = $model->getId();
		$data   = $this->input->getData();

		if ($model->isLocked($userId))
		{
			try
			{
				$model->checkIn($userId);
			}
			catch (LockedRecord $e)
			{
				// Redirect to the display task
				if ($customURL = $this->input->getBase64('returnurl', ''))
				{
					$customURL = base64_decode($customURL);
				}

				$eventName = 'onAfterApplySaveError';
				$result    = $this->triggerEvent($eventName, [&$data, $id, $e]);

				$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->container->componentName . '&view=' . $this->container->inflector->pluralize($this->view) . $this->getItemidURLSuffix();
				$this->setRedirect($url, $e->getMessage(), 'error');

				return false;
			}
		}

		// Set the layout to form, if it's not set in the URL
		if (is_null($this->layout))
		{
			$this->layout = 'form';
		}

		// Save the data
		$status = true;
		$error  = null;

		try
		{
			$eventName = 'onBeforeApplySave';
			$result    = $this->triggerEvent($eventName, [&$data]);

			if ($id != 0)
			{
				// Try to check-in the record if it's not a new one
				$model->unlock();
			}

			// Save the data
			$model->save($data);

			$eventName = 'onAfterApplySave';
			$result    = $this->triggerEvent($eventName, [&$data, $model->getId()]);

			$this->input->set('id', $model->getId());
		}
		catch (\Exception $e)
		{
			$status = false;
			$error  = $e->getMessage();

			$eventName = 'onAfterApplySaveError';
			$result    = $this->triggerEvent($eventName, [&$data, $model->getId(), $e]);
		}

		if (!$status)
		{
			// Cache the item data in the session. We may need to reuse them if the save fails.
			$itemData = $model->getData();

			$sessionKey = $this->viewName . '.savedata';
			$this->container->platform->setSessionVar($sessionKey, $itemData, $this->container->componentName);

			// Redirect on error
			$id = $model->getId();

			if ($customURL = $this->input->getBase64('returnurl', ''))
			{
				$customURL = base64_decode($customURL);
			}

			if (!empty($customURL))
			{
				$url = $customURL;
			}
			elseif ($id != 0)
			{
				$url = 'index.php?option=' . $this->container->componentName . '&view=' . $this->view . '&task=edit&id=' . $id . $this->getItemidURLSuffix();
			}
			else
			{
				$url = 'index.php?option=' . $this->container->componentName . '&view=' . $this->view . '&task=add' . $this->getItemidURLSuffix();
			}

			$this->setRedirect($url, $error, 'error');
		}
		else
		{
			$sessionKey = $this->viewName . '.savedata';
			$this->container->platform->setSessionVar($sessionKey, null, $this->container->componentName);
		}

		return $status;
	}

	/**
	 * Gets the applicable ACL privilege for the apply and save tasks. The value returned is:
	 * - @add if the record's ID is empty / record doesn't exist
	 * - True if the ACL privilege of the edit task (@edit) is allowed
	 * - @editown if the owner of the record (field user_id, userid or user) is the same as the logged in user
	 * - False if the record is not owned by the logged in user and the user doesn't have the @edit privilege
	 *
	 * @return bool|string
	 */
	protected function getACLForApplySave()
	{
		$model = $this->getModel();

		if (!$model->getId())
		{
			$this->getIDsFromRequest($model, true);
		}

		$id = $model->getId();

		if (!$id)
		{
			return '@add';
		}

		if ($this->checkACL('@edit'))
		{
			return true;
		}

		$user = $this->container->platform->getUser();
		$uid  = 0;

		if ($model->hasField('user_id'))
		{
			$uid = $model->getFieldValue('user_id');
		}
		elseif ($model->hasField('userid'))
		{
			$uid = $model->getFieldValue('userid');
		}
		elseif ($model->hasField('user'))
		{
			$uid = $model->getFieldValue('user');
		}

		if (!empty($uid) && !$user->guest && ($user->id == $uid))
		{
			return '@editown';
		}

		return false;
	}
}
