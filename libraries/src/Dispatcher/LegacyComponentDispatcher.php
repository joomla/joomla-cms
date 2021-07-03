<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Text;

/**
 * Base class for a legacy Joomla Dispatcher
 *
 * Executes the single entry file of a legacy component.
 *
 * @since  4.0.0
 */
class LegacyComponentDispatcher implements DispatcherInterface
{
	/**
	 * The application instance
	 *
	 * @var    CMSApplication
	 * @since  4.0.0
	 */
	private $app;

	/**
	 * Constructor for Dispatcher
	 *
	 * @param   CMSApplication  $app  The application instance
	 *
	 * @since   4.0.0
	 */
	public function __construct(CMSApplication $app)
	{
		$this->app = $app;
	}

	/**
	 * Dispatch a controller task. Redirecting the user if appropriate.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function dispatch()
	{
		$path = JPATH_COMPONENT . '/' . substr($this->app->scope, 4) . '.php';

		// If component file doesn't exist throw error
		if (!is_file($path))
		{
			throw new \Exception(Text::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 404);
		}

		$lang = $this->app->getLanguage();

		// Load common and local language files.
		$lang->load($this->app->scope, JPATH_BASE) || $lang->load($this->app->scope, JPATH_COMPONENT);

		// Execute the component
		$loader = static function ($path) {
			require_once $path;
		};
		$loader($path);
	}
}
