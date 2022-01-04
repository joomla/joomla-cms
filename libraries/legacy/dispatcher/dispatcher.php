<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Dispatcher
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Deprecated class placeholder.  You should use JEventDispatcher instead.
 *
 * @since       1.5
 * @deprecated  3.0
 */
class JDispatcher extends JEventDispatcher
{
	/**
	 * Constructor.
	 *
	 * @since   1.5
	 */
	public function __construct()
	{
		JLog::add('JDispatcher is deprecated. Use JEventDispatcher instead.', JLog::WARNING, 'deprecated');
		parent::__construct();
	}
}
