<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Webservices.Content
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\ApiRouter;
use Joomla\Router\Route;

/**
 * Web Services adapter for com_content.
 *
 * @since  4.0.0
 */
class PlgWebservicesContent extends CMSPlugin
{
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
	 * @param   object  &$subject  The object to observe.
	 * @param   array   $config    An optional associative array of configuration settings.
	 *
	 * @since  4.0.0
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		$this->allowedVerbs = $this->params->get('restverbs', []);
		$this->allowPublic  = $this->params->get('public', false);
	}

	/**
	 * Registers com_content's API's routes in the application
	 *
	 * @param   ApiRouter       &$router  The API Routing object
	 * @param   ApiApplication  $object   The API Application object
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function onBeforeApiRoute(&$router, $object)
	{
		if (!in_array($object->input->getMethod(), $this->allowedVerbs))
		{
			return;
		}

		$router->createCRUDRoutes(
			'v1/content/article',
			'articles',
			['component' => 'com_content'],
			$this->allowPublic,
			$this->allowedVerbs
		);

		$router->createCRUDRoutes(
			'v1/content/categories',
			'categories',
			['component' => 'com_categories', 'extension' => 'com_content'],
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
			'v1/fields/content/articles',
			'fields',
			['component' => 'com_fields', 'context' => 'com_content.article'],
			$this->allowPublic,
			$this->allowedVerbs
		);

		$router->createCRUDRoutes(
			'v1/fields/content/categories',
			'fields',
			['component' => 'com_fields', 'context' => 'com_content.categories'],
			$this->allowPublic,
			$this->allowedVerbs
		);

		$router->createCRUDRoutes(
			'v1/fields/groups/content/articles',
			'groups',
			['component' => 'com_fields', 'context' => 'com_content.article'],
			$this->allowPublic,
			$this->allowedVerbs
		);

		$router->createCRUDRoutes(
			'v1/fields/groups/content/categories',
			'groups',
			['component' => 'com_fields', 'context' => 'com_content.categories'],
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
			'type_alias' => 'com_content.article',
			'type_id'    => 1
		];
		$getDefaults = array_merge(['public' => $this->allowPublic], $defaults);

		$routes = [];

		if (in_array('GET', $this->allowedVerbs))
		{
			$routes[] = new Route(['GET'], 'v1/content/article/contenthistory/:id', 'history.displayList', ['id' => '(\d+)'], $getDefaults);
		}

		if (in_array('PATCH', $this->allowedVerbs))
		{
			$routes[] = new Route(['PATCH'], 'v1/content/article/contenthistory/keep/:id', 'history.keep', ['id' => '(\d+)'], $defaults);
		}

		if (in_array('DELETE', $this->allowedVerbs))
		{
			$routes[] = new Route(['DELETE'], 'v1/content/article/contenthistory/keep/:id', 'history.keep', ['id' => '(\d+)'], $defaults);
		}

		$router->addRoutes($routes);
	}
}
