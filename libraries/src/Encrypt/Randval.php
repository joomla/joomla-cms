<?php
/**
<<<<<<< HEAD:libraries/src/Encrypt/Randval.php
 * Joomla! Content Management System
 *
 * @copyright  (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
=======
 * @package     FrameworkOnFramework
 * @subpackage  utils
 * @copyright   Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @note        This file has been modified by the Joomla! Project and no longer reflects the original work of its author.
>>>>>>> d17dde8:libraries/fof/encrypt/randval.php
 */

namespace Joomla\CMS\Encrypt;

\defined('JPATH_PLATFORM') or die;

/**
 * Generates cryptographically-secure random values.
 *
 * @since    4.0.0
 */
class Randval implements RandValInterface
{
	/**
	 * Returns a cryptographically secure random value.
	 *
	 * This method allows us to quickly address any future issues if we ever find problems with PHP's random_bytes() on
	 * some weird host (you can't be too careful when releasing mass-distributed software).
	 *
	 * @param   integer  $bytes  How many bytes to return
	 *
	 * @return  string
	 */
	public function generate($bytes = 32)
	{
		return random_bytes($bytes);
	}
}
