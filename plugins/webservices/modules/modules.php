<?php
/**
 * @package     Joomla.Modules
 * @subpackage  Webservices.Modules
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
 * Web Services adapter for com_modules.
 *
 * @since  4.0.0
 */
class PlgWebservicesModules extends CMSPlugin
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
	 * Database Driver Instance
	 *
	 * @var    DatabaseDriver
	 * @since  4.0.0
	 */
	protected $db;
	
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $autoloadLanguage = true;

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

		$this->allowedVerbs = $this->params->get('restverbs', []);
		$this->allowPublic  = $this->params->get('public', false);
		$this->limit        = $this->params->get('limit', 0);
		$this->taskid       = (int) $this->params->get('taskid', 0);

		if ($this->taskid > $this->limit)
		{
			$endpoint = $this->_name . '.' . $this->_type . '.ratelimit';
			$this->app->input->set($endpoint, $this->limit, 'int');
		}
	}


/**
	 * Registers com_modules's API's routes in the application
	 *
	 * @param   ApiRouter  &$router  The API Routing object
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function onBeforeApiRoute(&$router)
	{
		$routes = array(
			new Route(
				['GET'], 'v1/modules/types/site', 'modules.getTypes', [],
				['public' => false, 'component' => 'com_modules', 'client_id' => 0]
			),
			new Route(
				['GET'], 'v1/modules/types/administrator', 'modules.getTypes', [],
				['public' => false, 'component' => 'com_modules', 'client_id' => 1]
			),
		);

		$router->addRoutes($routes);

		$router->createCRUDRoutes(
			'v1/modules/site',
			'modules',
			['component' => 'com_modules', 'client_id' => 0],
			$this->allowPublic,
			$this->allowedVerbs
		);

		$router->createCRUDRoutes(
			'v1/modules/administrator',
			'modules',
			['component' => 'com_modules', 'client_id' => 1],
			$this->allowPublic,
			$this->allowedVerbs
		);
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
	 * Count webservice public GET usage
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function onPublicGet($webservice)
	{
		if ($webservice !== 'modules.webservice')
		{
			return;
		}

		$taskid   = null;
		$db = $this->db;
		$type = 'webservices';
		$name = 'modules';

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
