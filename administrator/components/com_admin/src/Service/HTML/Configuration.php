<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Admin\Administrator\Service\HTML;

\defined('_JEXEC') or die;

/**
 * Class for rendering configuration values
 *
 * @since  4.0.0
 */
class Configuration
{
	/**
	 * Method to generate a string for a value
	 *
	 * @param   mixed  $value  The configuration value
	 *
	 * @return  string  Formatted and escaped string
	 *
	 * @since  4.0.0
	 */
	public function value($value): string
	{
		if (\is_bool($value))
		{
			return $value ? 'true' : 'false';
		}

		if (\is_array($value))
		{
			$value = implode(', ', $value);
		}

		return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
	}
}
