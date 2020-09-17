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
 * Folder exclusion filter. Excludes certain hosting directories.
 */
class Excludefolders extends Base
{
	public function __construct()
	{
		$this->object      = 'dir';
		$this->subtype     = 'all';
		$this->method      = 'direct';
		$this->filter_name = 'Excludefolders';

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

		// We take advantage of the filter class magic to inject our custom filters
		$this->filter_data[$root] = array(
			'awstats',
			'cgi-bin',
		);

		parent::__construct();
	}

}
