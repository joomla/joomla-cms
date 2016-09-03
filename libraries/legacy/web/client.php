<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Deprecated class placeholder. You should use JApplicationWebClient instead.
 *
 * @since       11.3
 * @deprecated  12.3 (Platform) & 4.0 (CMS)
 * @codeCoverageIgnore
 */
class JWebClient extends JApplicationWebClient
{
	/**
	 * Class constructor.
	 *
	 * @param   mixed  $userAgent       The optional user-agent string to parse.
	 * @param   mixed  $acceptEncoding  The optional client accept encoding string to parse.
	 * @param   mixed  $acceptLanguage  The optional client accept language string to parse.
	 *
	 * @since   11.3
	 */
	public function __construct($userAgent = null, $acceptEncoding = null, $acceptLanguage = null)
	{
		JLog::add('JWebClient is deprecated. Use JApplicationWebClient instead.', JLog::WARNING, 'deprecated');
		parent::__construct($userAgent, $acceptEncoding, $acceptLanguage);
	}
}
