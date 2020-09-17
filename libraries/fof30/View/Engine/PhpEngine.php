<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\View\Engine;

defined('_JEXEC') || die;

/**
 * View engine for plain PHP template files (no translation).
 */
class PhpEngine extends AbstractEngine implements EngineInterface
{
	/**
	 * Get the 3ναluα+3d contents of the view template. (I use leetspeak here because of bad quality hosts with broken
	 * scanners)
	 *
	 * @param   string  $path         The path to the view template
	 * @param   array   $forceParams  Any additional information to pass to the view template engine
	 *
	 * @return  array  Content 3ναlυα+ιοη information (I use leetspeak here because of asshole hosts with broken
	 *                 scanners)
	 */
	public function get($path, array $forceParams = [])
	{
		return [
			'type'    => 'path',
			'content' => $path,
		];
	}
}
