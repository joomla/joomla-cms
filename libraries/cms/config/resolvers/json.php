<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Config
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\Registry\Registry;

defined('JPATH_PLATFORM') or die;

/**
 * @package     Joomla.Libraries
 * @subpackage  Config
 * @since       3.3
 */
class JConfigResolverJson extends JConfigResolverFile implements JConfigResolverInterface {

	/**
	 * Load configuration file into config.
	 * 
	 * @return \Joomla\Registry\Registry
	 */
	protected function load()
	{
		if (is_null($this->file))
		{
			$this->file = JPATH_CONFIGURATION . '/configuration.json';
		}

		if (! file_exists($this->file))
		{
			throw new \RuntimeException('Unable to locate the configuration file.');
		}

		$this->instance = with(new JRegistry)->loadString(file_get_contents($this->file));
	}

	/**
	 * Export the configuration for future requests.
	 * 
	 * @return static
	 */
	public function export()
	{
		JFile::write($this->file, $this->config->objectToString());
	}

}