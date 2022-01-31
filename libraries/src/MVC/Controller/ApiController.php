<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Controller;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\View\JsonApiView;
use Joomla\CMS\Object\CMSObject;
use Joomla\Input\Input;
use Joomla\String\Inflector;
use Tobscure\JsonApi\Exception\InvalidParameterException;

/**
 * Base class for a Joomla API Controller
 *
 * Controller (controllers are where you put all the actual code) Provides basic
 * functionality, such as rendering views (aka displaying templates).
 *
 * @since  4.0.0
 */
class ApiController extends BaseController
{
	/**
	 * The content type of the item.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $contentType;

	/**
	 * The URL option for the component.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $option;

	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $text_prefix;

	/**
	 * The context for storing internal data, e.g. record.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $context;

	/**
	 * Items on a page
	 *
	 * @var  integer
	 */
	protected $itemsPerPage = 20;

	/**
	 * The model state to inject
	 *
	 * @var  CMSObject
	 */
	protected $modelState;

	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 *                                         Recognized key values include 'name', 'default_task', 'model_path', and
	 *                                         'view_path' (this list is not meant to be comprehensive).
	 * @param   MVCFactoryInterface  $factory  The factory.
	 * @param   CMSApplication       $app      The Application for the dispatcher
	 * @param   Input                $input    Input
	 *
	 * @since   4.0.0
	 * @throws  \Exception
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, ?CMSApplication $app = null, ?Input $input = null)
	{
		$this->modelState = new CMSObject;

		parent::__construct($config, $factory, $app, $input);

		// Guess the option as com_NameOfController
		if (empty($this->option))
		{
			$this->option = ComponentHelper::getComponentName($this, $this->getName());
		}

		// Guess the \Text message prefix. Defaults to the option.
		if (empty($this->text_prefix))
		{
			$this->text_prefix = strtoupper($this->option);
		}

		// Guess the context as the suffix, eg: OptionControllerContent.
		if (empty($this->context))
		{
			$r = null;

			if (!preg_match('/(.*)Controller(.*)/i', \get_class($this), $r))
			{
				throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_GET_NAME', __METHOD__), 500);
			}

			$this->context = str_replace('\\', '', strtolower($r[2]));
		}
	}

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
			$id = $this->input->get('id', 0, 'int');
		}

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

		$modelName = $this->input->get('model', Inflector::singularize($this->contentType));

		// Create the model, ignoring request data so we can safely set the state in the request from the controller
		$model = $this->getModel($modelName, '', ['ignore_request' => true, 'state' => $this->modelState]);

		if (!$model)
		{
			throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_MODEL_CREATE'));
		}

		try
		{
			$modelName = $model->getName();
		}
		catch (\Exception $e)
		{
			throw new \RuntimeException($e->getMessage());
		}

		$model->setState($modelName . '.id', $id);

		// Push the model into the view (as default)
		$view->setModel($model, true);

		$view->document = $this->app->getDocument();
		$view->displayItem();

		return $this;
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
		// Assemble pagination information (using recommended JsonApi pagination notation for offset strategy)
		$paginationInfo = $this->input->get('page', [], 'array');
		$limit          = null;
		$offset         = null;

		if (\array_key_exists('offset', $paginationInfo))
		{
			$offset = $paginationInfo['offset'];
			$this->modelState->set($this->context . '.limitstart', $offset);
		}

		if (\array_key_exists('limit', $paginationInfo))
		{
			$limit = $paginationInfo['limit'];
			$this->modelState->set($this->context . '.list.limit', $limit);
		}

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

		$modelName = $this->input->get('model', $this->contentType);

		/** @var ListModel $model */
		$model = $this->getModel($modelName, '', ['ignore_request' => true, 'state' => $this->modelState]);

		if (!$model)
		{
			throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_MODEL_CREATE'));
		}

		// Push the model into the view (as default)
		$view->setModel($model, true);

		if ($offset)
		{
			$model->setState('list.start', $offset);
		}

		/**
		 * Sanity check we don't have too much data being requested as regularly in html we automatically set it back to
		 * the last page of data. If there isn't a limit start then set
		 */
		if ($limit)
		{
			$model->setState('list.limit', $limit);
		}
		else
		{
			$model->setState('list.limit', $this->itemsPerPage);
		}

		if (!is_null($offset) && $offset > $model->getTotal())
		{
			throw new Exception\ResourceNotFound;
		}

		$view->document = $this->app->getDocument();

		$view->displayList();

		return $this;
	}

	/**
	 * Removes an item.
	 *
	 * @param   integer  $id  The primary key to delete item.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function delete($id = null)
	{
		if (!$this->app->getIdentity()->authorise('core.delete', $this->option))
		{
			throw new NotAllowed('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED', 403);
		}

		if ($id === null)
		{
			$id = $this->input->get('id', 0, 'int');
		}

		$modelName = $this->input->get('model', Inflector::singularize($this->contentType));

		/** @var \Joomla\CMS\MVC\Model\AdminModel $model */
		$model = $this->getModel($modelName, '', ['ignore_request' => true]);

		if (!$model)
		{
			throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_MODEL_CREATE'));
		}

		// Remove the item.
		if (!$model->delete($id))
		{
			if ($model->getError() !== false)
			{
				throw new \RuntimeException($model->getError(), 500);
			}

			throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_DELETE'), 500);
		}

		$this->app->setHeader('status', 204);
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
		// Access check.
		if (!$this->allowAdd())
		{
			throw new NotAllowed('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED', 403);
		}

		$recordId = $this->save();

		$this->displayItem($recordId);
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
		/** @var \Joomla\CMS\MVC\Model\AdminModel $model */
		$model = $this->getModel(Inflector::singularize($this->contentType));

		if (!$model)
		{
			throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_MODEL_CREATE'));
		}

		try
		{
			$table = $model->getTable();
		}
		catch (\Exception $e)
		{
			throw new \RuntimeException($e->getMessage());
		}

		$recordId = $this->input->getInt('id');

		if (!$recordId)
		{
			throw new Exception\ResourceNotFound(Text::_('JLIB_APPLICATION_ERROR_RECORD'), 404);
		}

		$key = $table->getKeyName();

		// Access check.
		if (!$this->allowEdit(array($key => $recordId), $key))
		{
			throw new NotAllowed('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED', 403);
		}

		// Attempt to check-out the new record for editing and redirect.
		if ($table->hasField('checked_out') && !$model->checkout($recordId))
		{
			// Check-out failed, display a notice but allow the user to see the record.
			throw new Exception\CheckinCheckout(Text::sprintf('JLIB_APPLICATION_ERROR_CHECKOUT_FAILED', $model->getError()));
		}

		$this->save($recordId);
		$this->displayItem($recordId);

		return $this;
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
		/** @var \Joomla\CMS\MVC\Model\AdminModel $model */
		$model = $this->getModel(Inflector::singularize($this->contentType));

		if (!$model)
		{
			throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_MODEL_CREATE'));
		}

		try
		{
			$table = $model->getTable();
		}
		catch (\Exception $e)
		{
			throw new \RuntimeException($e->getMessage());
		}

		$key        = $table->getKeyName();
		$data       = $this->input->get('data', json_decode($this->input->json->getRaw(), true), 'array');
		$checkin    = property_exists($table, $table->getColumnAlias('checked_out'));
		$data[$key] = $recordKey;

		if ($this->input->getMethod() === 'PATCH')
		{
			if ($recordKey && $table->load($recordKey))
			{
				$fields = $table->getFields();

				foreach ($fields as $field)
				{
					if (array_key_exists($field->Field, $data))
					{
						continue;
					}

					$data[$field->Field] = $table->{$field->Field};
				}
			}
		}

		$data = $this->preprocessSaveData($data);

		// @todo: Not the cleanest thing ever but it works...
		Form::addFormPath(JPATH_COMPONENT_ADMINISTRATOR . '/forms');

		// Needs to be set because com_fields needs the data in jform to determine the assigned catid
		$this->input->set('jform', $data);

		// Validate the posted data.
		$form = $model->getForm($data, false);

		if (!$form)
		{
			throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_FORM_CREATE'));
		}

		// Test whether the data is valid.
		$validData = $model->validate($form, $data);

		// Check for validation errors.
		if ($validData === false)
		{
			$errors   = $model->getErrors();
			$messages = [];

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = \count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof \Exception)
				{
					$messages[] = "{$errors[$i]->getMessage()}";
				}
				else
				{
					$messages[] = "{$errors[$i]}";
				}
			}

			throw new InvalidParameterException(implode("\n", $messages));
		}

		if (!isset($validData['tags']))
		{
			$validData['tags'] = array();
		}

		// Attempt to save the data.
		if (!$model->save($validData))
		{
			throw new Exception\Save(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
		}

		try
		{
			$modelName = $model->getName();
		}
		catch (\Exception $e)
		{
			throw new \RuntimeException($e->getMessage());
		}

		// Ensure we have the record ID in case we created a new article
		$recordId = $model->getState($modelName . '.id');

		if ($recordId === null)
		{
			throw new Exception\CheckinCheckout(Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
		}

		// Save succeeded, so check-in the record.
		if ($checkin && $model->checkin($recordId) === false)
		{
			throw new Exception\CheckinCheckout(Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
		}

		return $recordId;
	}

	/**
	 * Method to check if you can edit an existing record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key; default is id.
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		return $this->app->getIdentity()->authorise('core.edit', $this->option);
	}

	/**
	 * Method to check if you can add a new record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	protected function allowAdd($data = array())
	{
		$user = $this->app->getIdentity();

		return $user->authorise('core.create', $this->option) || \count($user->getAuthorisedCategories($this->option, 'core.create'));
	}

	/**
	 * Method to allow extended classes to manipulate the data to be saved for an extension.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	protected function preprocessSaveData(array $data): array
	{
		return $data;
	}
}
