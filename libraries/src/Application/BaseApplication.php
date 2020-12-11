<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Application;

\defined('JPATH_PLATFORM') or die;

use Joomla\Application\AbstractApplication;
use Joomla\CMS\Input\Input;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Registry\Registry;

/**
 * Joomla Platform Base Application Class
 *
 * @property-read  Input  $input  The application input object
 *
 * @since       3.0.0
 * @deprecated  5.0  Application classes should be based on \Joomla\Application\AbstractApplication
 */
abstract class BaseApplication extends AbstractApplication implements DispatcherAwareInterface
{
	use DispatcherAwareTrait, EventAware, IdentityAware;

	/**
	 * Class constructor.
	 *
	 * @param   Input     $input   An optional argument to provide dependency injection for the application's
	 *                             input object.  If the argument is a \JInput object that object will become
	 *                             the application's input object, otherwise a default input object is created.
	 * @param   Registry  $config  An optional argument to provide dependency injection for the application's
	 *                             config object.  If the argument is a Registry object that object will become
	 *                             the application's config object, otherwise a default config object is created.
	 *
	 * @since   3.0.0
	 */
	public function __construct(Input $input = null, Registry $config = null)
	{
		$this->input = $input instanceof Input ? $input : new Input;
		$this->config = $config instanceof Registry ? $config : new Registry;

		$this->initialise();
	}
}
