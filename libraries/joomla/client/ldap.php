<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Ldap\LdapClient;

/**
 * LDAP client class
 *
 * @since       12.1
 * @deprecated  4.0  Use Joomla\Ldap\LdapClient instead
 */
class JClientLdap extends LdapClient
{
	/**
	 * Constructor
	 *
	 * @param   object  $configObj  An object of configuration variables
	 *
	 * @since   11.1
	 */
	public function __construct($configObj = null)
	{
		JLog::add('JClientLdap is deprecated. Use Joomla\Ldap\LdapClient instead.', JLog::WARNING, 'deprecated');
		parent::__construct($configObj);
	}
}

/**
 * Deprecated class placeholder. You should use JClientLdap instead.
 *
 * @since       11.1
 * @deprecated  12.3 (Platform) & 4.0 (CMS)
 */
class JLDAP extends JClientLdap
{
	/**
	 * Constructor
	 *
	 * @param   object  $configObj  An object of configuration variables
	 *
	 * @since   11.1
	 */
	public function __construct($configObj = null)
	{
		JLog::add('JLDAP is deprecated. Use JClientLdap instead.', JLog::WARNING, 'deprecated');
		parent::__construct($configObj);
	}
}
