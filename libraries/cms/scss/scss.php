<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  SCSS
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

require_once JPATH_LIBRARIES . '/vendor/leafo/scssphp/scss.inc.php';
use Leafo\ScssPhp\Compiler;
use Leafo\ScssPhp\Server;

/**
 * Wrapper class for Compile
 *
 * @package     Joomla.Libraries
 * @subpackage  Scss
 * @since       __DEPLOY_VERSION__
 */
class JScss extends Server
{
	/**
	 * Wrapper function to set the format
	 *
	 * @param   string  $format  Filename to process
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function format($formatter = 'Crunched')
	{
		$compiler = new Compiler();
		$compiler->setFormatter('Leafo/ScssPhp/Formatter/' . ucfirst($formatter));
	}

	/**
	 * For documentation on this please see http://leafo.github.io/scssphp/docs
	 *
	 * @param   string  $path  The directory of your template.scss file
	 * @param   string  $dest  The directory
	 *
	 * @return  void
	 */
	public function compile($string = null, $name = null)
	{
		$this->allParsedFiles = array();

		$compile = new Server();

		return $this->compileFile($string, $name);
	}
}
