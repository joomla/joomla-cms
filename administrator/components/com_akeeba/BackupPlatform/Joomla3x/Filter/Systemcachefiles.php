<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Filter;

// Protection against direct access
defined('AKEEBAENGINE') || die();

use Akeeba\Engine\Factory;

/**
 * Files exclusion filter based on regular expressions
 */
class Systemcachefiles extends Base
{
	function __construct()
	{
		$this->object      = 'file';
		$this->subtype     = 'all';
		$this->method      = 'regex';
		$this->filter_name = 'Systemcachefiles';

		if (empty($this->filter_name))
		{
			$this->filter_name = strtolower(basename(__FILE__, '.php'));
		}

		parent::__construct();

		// Get the site's root
		$configuration = Factory::getConfiguration();

		if ($configuration->get('akeeba.platform.override_root', 0))
		{
			$root = $configuration->get('akeeba.platform.newroot', '[SITEROOT]');
		}
		else
		{
			$root = '[SITEROOT]';
		}

		$this->filter_data[$root] = array(
			'#/Thumbs\.db$#',
			'#^Thumbs\.db$#',
			'#/\.DS_Store$#i',
			'#^\.DS_Store$#i',
			'#^core\.[\d]{1,10}$#i',
		);
	}
}
