<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Deprecated class placeholder.  You should use JApplicationWeb instead.
 *
 * @since       1.7
 * @deprecated  2.5 Use JApplicationWeb instead.
 * @codeCoverageIgnore
 */
class JWeb extends JApplicationWeb
{
	/**
	 * Class constructor.
	 *
	 * @param   JInput                 $input   An optional argument to provide dependency injection for the application's
	 *                                          input object.  If the argument is a JInput object that object will become
	 *                                          the application's input object, otherwise a default input object is created.
	 * @param   Registry               $config  An optional argument to provide dependency injection for the application's
	 *                                          config object.  If the argument is a Registry object that object will become
	 *                                          the application's config object, otherwise a default config object is created.
	 * @param   JApplicationWebClient  $client  An optional argument to provide dependency injection for the application's
	 *                                          client object.  If the argument is a JApplicationWebClient object that object will become
	 *                                          the application's client object, otherwise a default client object is created.
	 *
	 * @since   1.7
	 * @deprecated  2.5 Use JApplicationWeb instead.
	 */
	public function __construct(JInput $input = null, Registry $config = null, JApplicationWebClient $client = null)
	{
		JLog::add('JWeb is deprecated. Use JApplicationWeb instead.', JLog::WARNING, 'deprecated');
		parent::__construct($input, $config, $client);
	}
}
