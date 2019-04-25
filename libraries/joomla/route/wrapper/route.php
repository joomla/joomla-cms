<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Wrapper class for JRoute
 *
 * @package     Joomla.Platform
 * @subpackage  Application
 * @since       3.4
 * @deprecated  4.0 Will be removed without replacement
 */
class JRouteWrapperRoute
{
	/**
	 * Helper wrapper method for _
	 *
	 * @param   string   $url    Absolute or Relative URI to Joomla resource.
	 * @param   boolean  $xhtml  Replace & by &amp; for XML compliance.
	 * @param   integer  $ssl    Secure state for the resolved URI.
	 *
	 * @return  string The translated humanly readable URL.
	 *
	 * @see     JRoute::_()
	 * @since   3.4
	 */
	public function _($url, $xhtml = true, $ssl = null)
	{
		return JRoute::_($url, $xhtml, $ssl);
	}
}
