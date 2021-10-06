<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Authentication.ldap
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Authentication\Ldap\Field;

\defined('_JEXEC') or die;

/**
 * Test Connection Field class for the LDAP Authentication Plugin.
 *
 * @since  __DEPLOY_VERSION__
 */
class TestConnectionField extends AbstractLdapField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'TestConnection';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $layout = 'field.testconnection';
}
