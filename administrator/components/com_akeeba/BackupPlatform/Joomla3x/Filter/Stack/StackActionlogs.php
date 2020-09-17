<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Filter\Stack;

use Akeeba\Engine\Filter\Base;

// Protection against direct access
defined('AKEEBAENGINE') || die();

/**
 * Exclude Joomla 3.9+ actions log table
 */
class StackActionlogs extends Base
{
	public function __construct()
	{
		$this->object  = 'dbobject';
		$this->subtype = 'content';
		$this->method  = 'api';

		parent::__construct();
	}

	protected function is_excluded_by_api($test, $root)
	{
		static $excluded = [
			'#__action_logs',
		];

		// Is it one of the blacklisted tables?
		if (in_array($test, $excluded))
		{
			return true;
		}

		// No match? Just include the file!
		return false;
	}

}
