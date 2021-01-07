<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Webservices.Contact
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\ApiRouter;
use Joomla\Registry\Registry;
use Joomla\Router\Route;

/**
 * Web Services adapter for com_contact.
 *
 * @since  4.0.0
 */
class PlgWebservicesContact extends CMSPlugin
{
	/**
	 * Application object
	 *
	 * @var    JApplicationCms
	 *
	 * @since  3.8.0
	 */
	protected $app;

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Database Driver Instance
	 *
	 * @var    DatabaseDriver
	 * @since  4.0.0
	 */
	protected $db;

	/**
	 * Allowed verbs
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $allowedVerbs = [];

	/**
	 * Allow public GET .
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $allowPublic = false;

	/**
	 * Constructor.
	 *
	 * @param   object  $subject  The object to observe.
	 * @param   array   $config   An optional associative array of configuration settings.
	 *
	 * @since  4.0.0
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		$endpoint           = $this->_name . '.' . $this->_type . '.';
		$this->allowedVerbs = $this->params->get('restverbs', []);
		$this->allowPublic  = $this->params->get('public', false);
		$this->limit        = $this->params->get('limit', 0);
		$this->taskid       = (int) $this->params->get('taskid', 0);
		$lastrun            = $this->params->get('lastrun', 0);
		$timeout            = $this->params->get('timeout', 1);
		$unit               = $this->params->get('unit', 86400);
		$timeout            = ($unit * $timeout);
		$xreset             = $lastrun + $timeout;

		if ($this->taskid > $this->limit)
		{
			$this->app->input->set($endpoint . 'ratelimit', $this->limit, 'int');
		}

		$this->app->input->set($endpoint . 'x-limit', $this->limit, 'int');
		$this->app->input->set($endpoint . 'x-remaining', $this->limit - $this->taskid, 'int');
		$this->app->input->set($endpoint . 'x-reset', $xreset, 'string');
	}

	/**
	 * Registers com_contact's API's routes in the application
	 *
	 * @param   ApiRouter  &$router  The API Routing object
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function onBeforeApiRoute(&$router)
	{
		$route = new Route(
			['POST'],
			'v1/contact/form/:id',
			'contact.submitForm',
			['id' => '(\d+)'],
			['component' => 'com_contact'],
			$this->allowPublic,
			$this->allowedVerbs
		);

		$router->addRoute($route);

		$router->createCRUDRoutes(
			'v1/contact',
			'contact',
			['component' => 'com_contact'],
			$this->allowPublic,
			$this->allowedVerbs
		);

		$router->createCRUDRoutes(
			'v1/contact/categories',
			'categories',
			['component' => 'com_categories', 'extension' => 'com_contact'],
			$this->allowPublic,
			$this->allowedVerbs
		);

		$this->createFieldsRoutes($router);

		$this->createContentHistoryRoutes($router);
	}

	/**
	 * Create fields routes
	 *
	 * @param   ApiRouter  &$router  The API Routing object
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	private function createFieldsRoutes(&$router)
	{
		$router->createCRUDRoutes(
			'v1/fields/contact/contact',
			'fields',
			['component' => 'com_fields', 'context' => 'com_contact.contact'],
			$this->allowPublic,
			$this->allowedVerbs
		);

		$router->createCRUDRoutes(
			'v1/fields/contact/mail',
			'fields',
			['component' => 'com_fields', 'context' => 'com_contact.mail'],
			$this->allowPublic,
			$this->allowedVerbs
		);

		$router->createCRUDRoutes(
			'v1/fields/contact/categories',
			'fields',
			['component' => 'com_fields', 'context' => 'com_contact.categories'],
			$this->allowPublic,
			$this->allowedVerbs
		);

		$router->createCRUDRoutes(
			'v1/fields/groups/contact/contact',
			'groups',
			['component' => 'com_fields', 'context' => 'com_contact.contact'],
			$this->allowPublic,
			$this->allowedVerbs
		);

		$router->createCRUDRoutes(
			'v1/fields/groups/contact/mail',
			'groups',
			['component' => 'com_fields', 'context' => 'com_contact.mail'],
			$this->allowPublic,
			$this->allowedVerbs
		);

		$router->createCRUDRoutes(
			'v1/fields/groups/contact/categories',
			'groups',
			['component' => 'com_fields', 'context' => 'com_contact.categories'],
			$this->allowPublic,
			$this->allowedVerbs
		);
	}

	/**
	 * Create contenthistory routes
	 *
	 * @param   ApiRouter  &$router  The API Routing object
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	private function createContentHistoryRoutes(&$router)
	{
		$defaults    = [
			'component'  => 'com_contenthistory',
			'type_alias' => 'com_contact.contact',
			'type_id'    => 2
		];
		$getDefaults = array_merge(['public' => $this->allowPublic], $defaults);

		$routes = [];

		if (in_array('GET', $this->allowedVerbs))
		{
			$routes[] = new Route(['GET'], 'v1/contact/contenthistory/:id', 'history.displayList', ['id' => '(\d+)'], $getDefaults);
		}

		if (in_array('PATCH', $this->allowedVerbs))
		{
			$routes[] = new Route(['PATCH'], 'v1/contact/contenthistory/keep/:id', 'history.keep', ['id' => '(\d+)'], $defaults);
		}

		if (in_array('DELETE', $this->allowedVerbs))
		{
			$routes[] = new Route(['DELETE'], 'v1/contact/contenthistory/:id', 'history.delete', ['id' => '(\d+)'], $defaults);
		}

		$router->addRoutes($routes);
	}

	/**
	 * Manage public GET request
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function onAfterApiRoute($object, $publicApi)
	{
		$status = $publicApi ? 0 : 1;
		$this->app->input->set('isPublicApi', $status);
	}

	/**
	 * Registers com_jobs API's routes in the application
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function onPublicGet($webservice)
	{
		if ($webservice !== 'contact.webservice')
		{
			return;
		}

		$taskid   = null;
		$db = $this->db;
		$type = 'webservices';
		$name = 'contact';

		$query = $db->getQuery(true);

		$query->select($db->quoteName(['extension_id', 'params']))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('element') . ' = :element')
			->where($db->quoteName('folder') . ' = :folder')
			->bind(':element', $name)
			->bind(':folder', $type);

		$db->setQuery($query);

		$params = $db->loadObject();

		$query  = $db->getQuery(true);
		$now    = Factory::getDate()->toSql();
		$query->update($db->quoteName('#__extensions'));

		// Update last run and taskid
		$taskParams = json_decode($params->params, true);
		$taskid = $taskParams['taskid'];

		$taskid++;
		$registry = new Registry($taskParams);
		$registry->set('taskid', $taskid);
		$jsonparam = $registry->toString('JSON');

		$query->set($db->quoteName('params') . ' = :params')
			->where($db->quoteName('element') . ' = :element')
			->where($db->quoteName('folder') . ' = :folder')
			->bind(':params', $jsonparam)
			->bind(':element', $name)
			->bind(':folder', $type);

		try
		{
			// Update the plugin parameters
			$result = $db->setQuery($query)->execute();
		}
		catch (RuntimeException $e)
		{
			// If we failed to execute
			return;
		}
	}
}
