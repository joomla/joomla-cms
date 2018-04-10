<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\Dispatcher;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Dispatcher class for com_media
 *
 * @since  4.0.0
 */
class MediaDispatcher extends Dispatcher
{
	/**
	 * The extension namespace
	 *
	 * @var    string
	 *
	 * @since  4.0.0
	 */
	protected $namespace = 'Joomla\\Component\\Media';

	/**
	 * Constructor
	 *
	 * @param   CMSApplication  $app    The application instance
	 * @param   Input           $input  The input instance
	 *
	 * @since   4.0.0
	 */
	public function __construct(\Joomla\CMS\Application\CMSApplication $app, \JInput $input = null)
	{
		parent::__construct($app, $input);

		// As default the view is set to featured, so we need to initialize it
		$this->input->set('view', 'media');
	}

	/**
	 * Load the language
	 *
	 * @since   4.0.0
	 *
	 * @return  void
	 */
	protected function loadLanguage()
	{
		// Load the administrator languages needed for the media manager
		$this->app->getLanguage()->load('', JPATH_ADMINISTRATOR);
		$this->app->getLanguage()->load($this->option, JPATH_ADMINISTRATOR);

		parent::loadLanguage();
	}

	/**
	 * Method to check component access permission
	 *
	 * @since   4.0.0
	 *
	 * @return  void
	 */
	protected function checkAccess()
	{
		$user   = $this->app->getIdentity();
		$asset  = $this->input->get('asset');
		$author = $this->input->get('author');

		// Access check
		if (!$user->authorise('core.manage', 'com_media')
			&& (!$asset || (!$user->authorise('core.edit', $asset)
			&& !$user->authorise('core.create', $asset)
			&& count($user->getAuthorisedCategories($asset, 'core.create')) == 0)
			&& !($user->id == $author && $user->authorise('core.edit.own', $asset))))
		{
			throw new \Joomla\CMS\Access\Exception\Notallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}
	}

	/**
	 * Get a controller from the component
	 *
	 * @param   string  $name    Controller name
	 * @param   string  $client  Optional client (like Administrator, Site etc.)
	 * @param   array   $config  Optional controller config
	 *
	 * @return  BaseController
	 *
	 * @since   4.0.0
	 */
	public function getController(string $name, string $client = '', array $config = array()): BaseController
	{
		$config['base_path'] = JPATH_ADMINISTRATOR . '/components/com_media';

		// Force to load the admin controller
		return parent::getController($name, 'Administrator', $config);
	}
}
