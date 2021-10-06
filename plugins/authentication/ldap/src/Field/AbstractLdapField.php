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

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;

/**
 * @since  __DEPLOY_VERSION__
 */
abstract class AbstractLdapField extends FormField
{
	/**
	 * Get the layouts paths
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getLayoutPaths()
	{
		$template = Factory::getApplication()->getTemplate();

		return [
			JPATH_ADMINISTRATOR . '/templates/' . $template . '/html/layouts/plugins/authentication/ldap',
			JPATH_PLUGINS . '/authentication/ldap/layouts',
			JPATH_SITE . '/layouts',
		];
	}
}
