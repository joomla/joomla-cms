<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Application\AbstractApplication;
use Joomla\Cms\Application\EventAware;
use Joomla\Cms\Application\IdentityAware;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Registry\Registry;

/**
 * Joomla Platform Base Application Class
 *
 * @property-read  JInput  $input  The application input object
 *
 * @since       12.1
 * @deprecated  5.0  Application classes should be based on \Joomla\Application\AbstractApplication
 */
abstract class JApplicationBase extends AbstractApplication implements DispatcherAwareInterface
{
	use DispatcherAwareTrait, EventAware, IdentityAware;

	/**
	 * Class constructor.
	 *
	 * @param   JInput    $input   An optional argument to provide dependency injection for the application's
	 *                             input object.  If the argument is a JInput object that object will become
	 *                             the application's input object, otherwise a default input object is created.
	 * @param   Registry  $config  An optional argument to provide dependency injection for the application's
	 *                             config object.  If the argument is a Registry object that object will become
	 *                             the application's config object, otherwise a default config object is created.
	 *
	 * @since   12.1
	 */
	public function __construct(JInput $input = null, Registry $config = null)
	{
		$this->input = $input instanceof JInput ? $input : new JInput;
		$this->config = $config instanceof Registry ? $config : new Registry;

		$this->initialise();
	}
}
