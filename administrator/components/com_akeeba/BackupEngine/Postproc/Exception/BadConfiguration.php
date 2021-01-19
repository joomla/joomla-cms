<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Postproc\Exception;

defined('AKEEBAENGINE') || die();

use RuntimeException;

/**
 * Indicates an error with the post-processing engine's configuration
 */
class BadConfiguration extends RuntimeException
{
}
