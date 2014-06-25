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
class JConfigResolverPhp extends JConfigResolverFile implements JConfigResolverInterface {

	/**
	 * Load configuration file into config.
	 * 
	 * @return \Joomla\Registry\Registry
	 */
	protected function load()
	{
		if (! $this->file)
		{
			$this->file = JPATH_CONFIGURATION . '/configuration.php';
		}

		if ((! class_exists('JConfig')) and file_exists($this->file))
		{
			require $this->file;
		}

		if (class_exists('JConfig'))
		{
			throw new \RuntimeException('Unable to load configuration file.');
		}

		$this->instance = with(new JRegistry)->loadObject(new JConfig);
	}

	/**
	 * Export the configuration for future requests.
	 * 
	 * @return static
	 */
	public function export()
	{
		JFile::write($this->file, $this->config->objectToString('JConfig', ['closingtag' => false]));
	}

}