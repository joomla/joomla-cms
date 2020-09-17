<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\View\Compiler;

defined('_JEXEC') || die;

interface CompilerInterface
{
	/**
	 * Are the results of this compiler engine cacheable? If the engine makes use of the forcedParams it must return
	 * false.
	 *
	 * @return  mixed
	 */
	public function isCacheable();

	/**
	 * Compile a view template into PHP and HTML
	 *
	 * @param   string  $path         The absolute filesystem path of the view template
	 * @param   array   $forceParams  Any parameters to force (only for engines returning raw HTML)
	 *
	 * @return mixed
	 */
	public function compile($path, array $forceParams = []);

	/**
	 * Returns the file extension supported by this compiler
	 *
	 * @return  string
	 *
	 * @since   3.3.1
	 */
	public function getFileExtension();
}
