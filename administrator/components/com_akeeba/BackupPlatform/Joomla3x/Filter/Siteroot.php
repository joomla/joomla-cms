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
 * Add site's root to the backup set.
 */
class Siteroot extends Base
{
	public function __construct()
	{
		// This is a directory inclusion filter.
		$this->object      = 'dir';
		$this->subtype     = 'inclusion';
		$this->method      = 'direct';
		$this->filter_name = 'Siteroot';

		// Directory inclusion format:
		// array(real_directory, add_path)
		$add_path = null; // A null add_path means that we dump this dir's contents in the archive's root

		// We take advantage of the filter class magic to inject our custom filters
		$configuration = Factory::getConfiguration();

		if ($configuration->get('akeeba.platform.override_root', 0))
		{
			$root = $configuration->get('akeeba.platform.newroot', '[SITEROOT]');
		}
		else
		{
			$root = '[SITEROOT]';
		}

		$this->filter_data[] = array(
			$root,
			$add_path
		);

		parent::__construct();
	}
}
