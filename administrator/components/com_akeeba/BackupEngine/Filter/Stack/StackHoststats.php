<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Filter\Stack;

defined('AKEEBAENGINE') || die();

use Akeeba\Engine\Filter\Base;

/**
 * Exclude folders and files belonging to the host web stat (ie Webalizer)
 */
class StackHoststats extends Base
{
	public function __construct()
	{
		$this->object  = 'dir';
		$this->subtype = 'all';
		$this->method  = 'api';

		if (empty($this->filter_name))
		{
			$this->filter_name = strtolower(basename(__FILE__, '.php'));
		}

		parent::__construct();
	}

	protected function is_excluded_by_api($test, $root)
	{
		if ($test == 'stats')
		{
			return true;
		}

		// No match? Just include the file!
		return false;
	}

}
